<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'admin_email' => 'required|email',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:20',
            'app_logo' => 'nullable|image|max:2048',
            'app_favicon' => 'nullable|image|max:1024',
            'facebook_app_id' => 'nullable|string|max:255',
            'facebook_app_secret' => 'nullable|string|max:255',
            'facebook_webhook_verify_token' => 'nullable|string|max:255',
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
        ]);

        $settings = $request->only([
            'app_name', 'app_description', 'admin_email', 
            'company_address', 'company_phone',
            'facebook_app_id', 'facebook_app_secret', 
            'facebook_webhook_verify_token', 'currency_code', 'currency_symbol'
        ]);

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $logoPath = $request->file('app_logo')->store('logos', 'public');
            $settings['app_logo'] = $logoPath;
        }

        // Handle favicon upload
        if ($request->hasFile('app_favicon')) {
            $faviconPath = $request->file('app_favicon')->store('favicons', 'public');
            $settings['app_favicon'] = $faviconPath;
        }

        // Update or create settings
        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Clear settings cache
        \App\Helpers\SettingsHelper::clearCache();

        return redirect()->route('admin.settings.general')
            ->with('success', 'General settings updated successfully.');
    }

    public function users()
    {
        $users = Admin::orderBy('created_at', 'desc')->get();
        
        return view('admin.settings.users', compact('users'));
    }

    public function payments()
    {
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        
        return view('admin.settings.payments', compact('settings'));
    }

    public function updatePayments(Request $request)
    {
        $request->validate([
            'payment_currency' => 'required|string|max:10',
            'bkash_app_key' => 'nullable|string|max:255',
            'bkash_app_secret' => 'nullable|string|max:255',
            'bkash_username' => 'nullable|string|max:255',
            'bkash_password' => 'nullable|string|max:255',
            'bkash_sandbox_mode' => 'boolean',
            'nagad_merchant_id' => 'nullable|string|max:255',
            'nagad_merchant_private_key' => 'nullable|string|max:1000',
            'nagad_sandbox_mode' => 'boolean',
            'stripe_publishable_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'stripe_webhook_secret' => 'nullable|string|max:255',
        ]);

        $settings = $request->only([
            'payment_currency', 'bkash_app_key', 'bkash_app_secret',
            'bkash_username', 'bkash_password', 'bkash_sandbox_mode',
            'nagad_merchant_id', 'nagad_merchant_private_key', 'nagad_sandbox_mode',
            'stripe_publishable_key', 'stripe_secret_key', 'stripe_webhook_secret'
        ]);

        // Convert boolean values
        $settings['bkash_sandbox_mode'] = $request->boolean('bkash_sandbox_mode');
        $settings['nagad_sandbox_mode'] = $request->boolean('nagad_sandbox_mode');

        // Update or create settings
        foreach ($settings as $key => $value) {
            SystemSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Clear settings cache
        \App\Helpers\SettingsHelper::clearCache();

        return redirect()->route('admin.settings.payments')
            ->with('success', 'Payment settings updated successfully.');
    }

    public function createUser(Request $request)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager',
            'permissions' => 'array',
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_active' => true,
        ]);

        return redirect()->route('admin.settings.users')
            ->with('success', 'Admin user created successfully.');
    }

    public function updateUser(Request $request, Admin $admin)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,manager',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);

        return redirect()->route('admin.settings.users')
            ->with('success', 'Admin user updated successfully.');
    }

    public function deleteUser(Admin $admin)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($admin->id === auth('admin')->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->route('admin.settings.users')
            ->with('success', 'Admin user deleted successfully.');
    }
}