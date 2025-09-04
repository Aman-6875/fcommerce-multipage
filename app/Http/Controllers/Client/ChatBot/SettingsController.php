<?php

namespace App\Http\Controllers\Client\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\PageBusinessConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return redirect()->route('client.facebook.index')->with('warning', 'Please connect a Facebook page first.');
        }
        
        $config = PageBusinessConfig::where('facebook_page_id', $pageId)->first();
        
        return view('client.chat-bot.settings.index', compact('config'));
    }
    
    public function update(Request $request)
    {
        $client = Auth::user();
        $pageId = $client->getSelectedPageId();
        
        if (!$pageId) {
            return redirect()->route('client.facebook.index')->with('warning', 'Please connect a Facebook page first.');
        }
        
        $validated = $request->validate([
            'business_type' => 'required|in:software,restaurant,salon,ecommerce,service,general',
            'inquiry_type' => 'required|in:order,booking,appointment,consultation,inquiry',
            'currency' => 'nullable|in:BDT,USD,EUR,GBP',
            'default_language' => 'nullable|in:en,bn',
            'company_name' => 'nullable|string|max:255',
            'company_description_en' => 'nullable|string',
            'company_description_bn' => 'nullable|string',
            'welcome_message_en' => 'nullable|string',
            'welcome_message_bn' => 'nullable|string',
            'budget_options' => 'nullable|array',
            'budget_options.*' => 'nullable|string|max:100',
            'collect_name' => 'boolean',
            'collect_phone' => 'boolean',
            'collect_address' => 'boolean',
            'collect_budget' => 'boolean',
            'collect_requirements' => 'boolean',
            'auto_assign_numbers' => 'boolean',
        ]);
        
        // Clean up budget options
        if (isset($validated['budget_options'])) {
            $validated['budget_options'] = array_filter($validated['budget_options'], function($option) {
                return !empty(trim($option));
            });
        }
        
        $config = PageBusinessConfig::updateOrCreate(
            [
                'facebook_page_id' => $pageId,
                'client_id' => $client->id,
            ],
            $validated
        );
        
        return redirect()->route('client.chat-bot.settings.index')
                        ->with('success', __('client.business_settings_updated_successfully'));
    }
}
