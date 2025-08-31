<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    public function updateAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . auth('client')->id(),
            'phone' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
        ]);

        $client = auth('client')->user();
        $profileData = $client->profile_data ?? [];
        $profileData['business_name'] = $request->business_name;

        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'profile_data' => $profileData,
        ]);

        return redirect()->route('client.settings')->with('success', __('client.account_updated_successfully'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $client = auth('client')->user();

        if (!Hash::check($request->current_password, $client->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('client.current_password_incorrect')],
            ]);
        }

        $client->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('client.settings')->with('success', __('client.password_updated_successfully'));
    }

    public function updateNotifications(Request $request)
    {
        $client = auth('client')->user();
        $settings = $client->settings ?? [];

        $settings['email_notifications'] = $request->input('email_notifications', []);
        $settings['browser_notifications'] = $request->boolean('browser_notifications');
        $settings['sound_notifications'] = $request->boolean('sound_notifications');

        $client->update(['settings' => $settings]);

        return redirect()->route('client.settings')->with('success', __('client.notifications_updated_successfully'));
    }

    public function updateBusiness(Request $request)
    {
        $request->validate([
            'business_type' => 'nullable|in:ecommerce,service,both',
            'currency' => 'required|in:BDT,USD,EUR',
            'timezone' => 'required|string',
            'language' => 'required|in:bn,en',
        ]);

        $client = auth('client')->user();
        $profileData = $client->profile_data ?? [];
        $settings = $client->settings ?? [];

        $profileData['business_type'] = $request->business_type;
        $settings['currency'] = $request->currency;
        $settings['timezone'] = $request->timezone;
        $settings['language'] = $request->language;

        $client->update([
            'profile_data' => $profileData,
            'settings' => $settings,
        ]);

        return redirect()->route('client.settings')->with('success', __('client.business_settings_updated_successfully'));
    }

    public function deleteAccount(Request $request)
    {
        $client = auth('client')->user();

        // Log out the user
        auth('client')->logout();

        // Delete the client account (this will cascade delete related data if configured)
        $client->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', __('client.account_deleted_successfully'));
    }
}