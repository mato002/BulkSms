<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        // Get user statistics based on client_id
        $clientId = $user->client_id;
        
        $stats = [
            'total_messages' => $clientId ? Message::where('client_id', $clientId)->count() : 0,
            'total_campaigns' => $clientId ? Campaign::where('client_id', $clientId)->count() : 0,
            'total_contacts' => $clientId ? Contact::where('client_id', $clientId)->count() : 0,
            'messages_sent' => $clientId ? Message::where('client_id', $clientId)->where('status', 'sent')->count() : 0,
            'messages_failed' => $clientId ? Message::where('client_id', $clientId)->where('status', 'failed')->count() : 0,
        ];
        
        // Get recent activity for this client
        $recentActivity = $clientId 
            ? Message::where('client_id', $clientId)
                ->latest()
                ->take(10)
                ->get()
            : collect();
        
        return view('profile.index', compact('user', 'stats', 'recentActivity'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old avatar if exists
        if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
            Storage::delete('public/' . $user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Avatar updated successfully.');
    }

    public function deleteAvatar()
    {
        $user = auth()->user();

        if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
            Storage::delete('public/' . $user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return back()->with('success', 'Avatar removed successfully.');
    }

    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'marketing_emails' => 'nullable|boolean',
            'message_alerts' => 'nullable|boolean',
            'campaign_updates' => 'nullable|boolean',
        ]);

        // Store preferences as JSON in user preferences column
        $preferences = array_merge($user->preferences ?? [], $validated);
        $user->preferences = $preferences;
        $user->save();

        return back()->with('success', 'Preferences updated successfully.');
    }
}


