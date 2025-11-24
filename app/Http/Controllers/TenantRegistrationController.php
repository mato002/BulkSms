<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Client;
use App\Models\Channel;
use App\Mail\TenantWelcomeEmail;
use App\Mail\TenantApprovalEmail;

class TenantRegistrationController extends Controller
{
    /**
     * Show tenant registration form
     */
    public function showRegistration()
    {
        return view('tenant.register');
    }

    /**
     * Process tenant registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'business_type' => 'required|string|max:100',
            'expected_volume' => 'required|string|in:low,medium,high,enterprise',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ], [
            'terms.accepted' => 'You must accept the terms and conditions.',
            'email.unique' => 'This email is already registered.',
        ]);

        DB::beginTransaction();
        
        try {
            // Generate unique API key
            $apiKey = 'sk_' . Str::random(32);
            
            // Create client/tenant
            $client = Client::create([
                'name' => $validated['contact_person'],
                'contact' => $validated['email'],
                'sender_id' => $this->generateSenderId($validated['company_name']),
                'company_name' => $validated['company_name'],
                'balance' => 0.00,
                'price_per_unit' => $this->getPricePerUnit($validated['expected_volume']),
                'api_key' => $apiKey,
                'status' => false, // Pending approval
                'tier' => $validated['expected_volume'],
                'is_test_mode' => true, // Start in test mode
                'settings' => [
                    'business_type' => $validated['business_type'],
                    'phone' => $validated['phone'],
                    'registration_date' => now()->toDateString(),
                    'approval_status' => 'pending',
                ],
            ]);

            // Create user account
            $user = User::create([
                'name' => $validated['contact_person'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'client_id' => $client->id,
                'role' => 'user', // Tenant users are regular users, not admins
            ]);

            // Create default SMS channel for the tenant
            // Uses system's Onfon credentials (shared gateway)
            $this->createDefaultSmsChannel($client);

            DB::commit();

            // Send emails after transaction commit (non-blocking)
            // This prevents email failures from blocking registration
            try {
                Mail::to($user->email)->send(new TenantWelcomeEmail($client, $user));
            } catch (\Exception $emailException) {
                // Log email error but don't fail registration
                \Log::warning('Failed to send welcome email to tenant', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
            }

            try {
                $this->notifyAdminForApproval($client, $user);
            } catch (\Exception $emailException) {
                // Log email error but don't fail registration
                \Log::warning('Failed to send admin notification email', [
                    'client_id' => $client->id,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString()
                ]);
            }

            return redirect()->route('tenant.registration.success')
                ->with('success', 'Registration successful! Please check your email for next steps.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Re-throw validation exceptions to show proper validation errors
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Log database errors with full details
            \Log::error('Database error during tenant registration', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            
            return back()->withErrors([
                'error' => 'Registration failed due to a database error. Please try again or contact support if the problem persists.'
            ])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log all other errors with full details for debugging
            \Log::error('Error during tenant registration', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            
            // In development, show more details. In production, show user-friendly message
            $errorMessage = config('app.debug') 
                ? 'Registration failed: ' . $e->getMessage() 
                : 'Registration failed. Please try again or contact support.';
            
            return back()->withErrors([
                'error' => $errorMessage
            ])->withInput();
        }
    }

    /**
     * Show registration success page
     */
    public function showSuccess()
    {
        return view('tenant.registration-success');
    }

    /**
     * Generate unique sender ID
     */
    private function generateSenderId($companyName)
    {
        $baseSenderId = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $companyName));
        $senderId = substr($baseSenderId, 0, 8);
        
        // Ensure uniqueness
        $counter = 1;
        $originalSenderId = $senderId;
        
        while (Client::where('sender_id', $senderId)->exists()) {
            $senderId = $originalSenderId . $counter;
            $counter++;
        }
        
        return $senderId;
    }

    /**
     * Get price per unit based on expected volume
     */
    private function getPricePerUnit($volume)
    {
        return match($volume) {
            'low' => 2.50,      // < 1000 messages/month
            'medium' => 2.00,   // 1000-10000 messages/month
            'high' => 1.50,     // 10000-100000 messages/month
            'enterprise' => 1.00, // > 100000 messages/month
            default => 2.50,
        };
    }

    /**
     * Create default SMS channel for tenant
     * Uses system's shared Onfon credentials
     */
    private function createDefaultSmsChannel($client)
    {
        try {
            // Check if channel already exists
            $existingChannel = Channel::where('client_id', $client->id)
                ->where('name', 'sms')
                ->first();

            if ($existingChannel) {
                // Update existing channel to ensure it's active
                $existingChannel->update([
                    'active' => true,
                    'provider' => 'onfon',
                ]);
                return;
            }

            // Get system Onfon credentials from config
            $onfonConfig = config('sms.gateways.onfon', []);
            
            // Create default SMS channel using system's shared Onfon gateway
            Channel::create([
                'client_id' => $client->id,
                'name' => 'sms',
                'provider' => 'onfon',
                'credentials' => [
                    'api_key' => $onfonConfig['api_key'] ?? env('ONFON_API_KEY', ''),
                    'client_id' => $onfonConfig['client_id'] ?? env('ONFON_CLIENT_ID', ''),
                    'access_key_header' => env('ONFON_ACCESS_KEY_HEADER', '8oj1kheKHtCX6RiiOOI1sNS9Ir88CXnB'),
                    'default_sender' => $client->sender_id,
                    'base_url' => $onfonConfig['url'] ?? 'https://api.onfonmedia.co.ke/v1/sms/SendBulkSMS',
                ],
                'active' => true,
                'config' => [
                    'uses_system_gateway' => true,
                    'created_at_registration' => true,
                ],
            ]);

            \Log::info('Default SMS channel created for tenant', [
                'client_id' => $client->id,
                'sender_id' => $client->sender_id,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            \Log::error('Failed to create default SMS channel for tenant', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Notify admin for tenant approval
     */
    private function notifyAdminForApproval($client, $user)
    {
        try {
            // Get admin users (you can customize this logic)
            $adminUsers = User::where('role', 'admin')->orWhere('client_id', 1)->get();
            
            foreach ($adminUsers as $admin) {
                try {
                    Mail::to($admin->email)->send(new TenantApprovalEmail($client, $user));
                } catch (\Exception $e) {
                    // Log individual email failures but continue with others
                    \Log::warning('Failed to send approval email to admin', [
                        'admin_email' => $admin->email,
                        'client_id' => $client->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Log for admin dashboard
            \Log::info('New tenant registration pending approval', [
                'client_id' => $client->id,
                'company_name' => $client->company_name,
                'contact_email' => $user->email,
                'tier' => $client->tier,
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw - this is non-critical
            \Log::error('Error in notifyAdminForApproval', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

