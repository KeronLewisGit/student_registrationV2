<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Self-service profile for every signed-in user: name and contact details,
 * password change, and sign-in security.
 */
class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit(Request $request)
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'signIns' => ActivityLog::where('user_id', $user->id)
                ->where('category', 'auth')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'lastChange' => ActivityLog::where('user_id', $user->id)
                ->whereIn('action', ['password-changed', 'password-reset'])
                ->latest('created_at')
                ->first(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'job_title' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:40',
            'current_password' => [Rule::requiredIf(fn () => strcasecmp($request->input('email', ''), $user->email) !== 0), 'nullable', 'current_password'],
        ], [
            'current_password.required' => 'Enter your current password to change your email address.',
            'current_password.current_password' => 'That is not your current password.',
        ]);

        $changes = [];
        foreach (['first_name', 'last_name', 'email', 'job_title', 'phone'] as $field) {
            $new = $validated[$field] ?? null;
            if ((string) $user->{$field} !== (string) $new) {
                $changes[$field] = ['from' => $user->{$field}, 'to' => $new];
            }
        }

        $user->fill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'job_title' => $validated['job_title'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ])->save();

        if ($changes) {
            ActivityLog::log('user', 'profile-updated', 'Updated own profile', ['subject' => $user, 'changes' => $changes]);
        }

        return redirect()->route('profile.edit')->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(12)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'That is not your current password.',
            'password.different' => 'Choose a password you have not used just now.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Keep this session, drop every other one that used the old password
        Auth::logoutOtherDevices($request->input('password'));

        ActivityLog::log('user', 'password-changed', 'Changed own password', ['subject' => $user]);

        return redirect()->route('profile.edit')->with('success', 'Your password has been changed. Other devices have been signed out.');
    }

    public function signOutOtherDevices(Request $request)
    {
        $request->validateWithBag('sessions', [
            'password_confirm' => ['required', 'current_password'],
        ], ['password_confirm.current_password' => 'That is not your current password.']);

        Auth::logoutOtherDevices($request->input('password_confirm'));

        ActivityLog::log('user', 'sessions-revoked', 'Signed out of all other devices', ['subject' => $request->user()]);

        return redirect()->route('profile.edit')->with('success', 'All other devices have been signed out.');
    }
}
