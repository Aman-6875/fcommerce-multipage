<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $client = auth('client')->user();

        // Check if client has reached free limits
        $freeLimitsReached = $client->hasReachedFreeLimits();
        
        // Calculate days remaining in trial
        $trialDaysLeft = 0;
        if ($client->isFree()) {
            $trialDaysLeft = max(0, 10 - floor($client->created_at->diffInDays(now())));
        }

        $stats = [
            'facebook_pages' => $client->facebookPages()->count(),
            'connected_pages' => $client->facebookPages()->where('is_connected', true)->count(),
            'total_customers' => $client->customers()->count(),
            'active_customers' => $client->customers()->where('status', 'active')->count(),
            'total_orders' => $client->orders()->count(),
            'pending_orders' => $client->orders()->where('status', 'pending')->count(),
            'total_services' => $client->services()->count(),
            'upcoming_services' => $client->services()->where('booking_date', '>=', today())->count(),
            'messages_sent' => CustomerMessage::where('client_id', $client->id)
                ->where('message_type', 'outgoing')
                ->count(),
            'messages_received' => CustomerMessage::where('client_id', $client->id)
                ->where('message_type', 'incoming')
                ->count(),
        ];

        // Recent activities
        $recent_customers = $client->customers()->latest()->limit(5)->get();
        $recent_orders = $client->orders()->latest()->limit(5)->get();
        $recent_messages = CustomerMessage::where('client_id', $client->id)
            ->latest()
            ->limit(10)
            ->with('customer')
            ->get();

        return view('client.dashboard', compact(
            'client',
            'stats',
            'freeLimitsReached',
            'trialDaysLeft',
            'recent_customers',
            'recent_orders',
            'recent_messages'
        ));
    }
}
