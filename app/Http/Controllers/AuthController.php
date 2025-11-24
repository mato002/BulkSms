<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Models\Notification as CustomNotification;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            // Set client_id in session
            $user = Auth::user();
            session(['client_id' => $user->client_id ?? 1]);

            // Security notification: successful login
            try {
                CustomNotification::create([
                    'client_id' => $user->client_id ?? 1,
                    'user_id' => $user->id,
                    'type' => 'security_login_success',
                    'title' => 'Login Successful',
                    'message' => 'Successful login from ' . $request->ip(),
                    'icon' => 'bi-shield-check',
                    'color' => 'success',
                    'metadata' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'time' => now()->toDateTimeString(),
                    ],
                ]);

                // Also notify first admin (if not the same user)
                $admin = User::where('role', 'admin')->first();
                if ($admin && $admin->id !== $user->id) {
                    CustomNotification::create([
                        'client_id' => $admin->client_id ?? 1,
                        'user_id' => $admin->id,
                        'type' => 'security_login_success_user',
                        'title' => 'User Login',
                        'message' => $user->email . ' logged in from ' . $request->ip(),
                        'icon' => 'bi-person-check',
                        'color' => 'info',
                        'metadata' => [
                            'user_email' => $user->email,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'time' => now()->toDateTimeString(),
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Do not block login on notification failure
            }

            // Redirect tenants: if inactive, go to onboarding/payment; if active, go to dashboard. Admins to main dashboard
            if ($user->client_id && $user->client_id !== 1) {
                $client = $user->client;
                if ($client && !$client->status) {
                    return redirect()->route('tenant.onboarding')
                        ->with('info', 'Your account is pending activation. Complete onboarding to continue.');
                }
                return redirect()->route('tenant.dashboard');
            }

            return redirect()->intended('/');
        }

        // Security notification: failed login attempt
        try {
            $user = User::where('email', $request->input('email'))->first();
            $admin = User::where('role', 'admin')->first();

            if ($user) {
                CustomNotification::create([
                    'client_id' => $user->client_id ?? 1,
                    'user_id' => $user->id,
                    'type' => 'security_login_failed',
                    'title' => 'Failed Login Attempt',
                    'message' => 'Failed login attempt from ' . $request->ip(),
                    'icon' => 'bi-shield-exclamation',
                    'color' => 'danger',
                    'metadata' => [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'time' => now()->toDateTimeString(),
                    ],
                ]);
            }

            if ($admin) {
                CustomNotification::create([
                    'client_id' => $admin->client_id ?? 1,
                    'user_id' => $admin->id,
                    'type' => 'security_login_failed_admin',
                    'title' => 'Failed Login Attempt',
                    'message' => 'Failed login for ' . $request->input('email') . ' from ' . $request->ip(),
                    'icon' => 'bi-shield-exclamation',
                    'color' => 'warning',
                    'metadata' => [
                        'attempted_email' => $request->input('email'),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'time' => now()->toDateTimeString(),
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_id' => 1, // Default client
        ]);

        Auth::login($user);
        session(['client_id' => 1]);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Password Reset Methods
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}

