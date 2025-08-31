<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Service;
use App\Models\FacebookPage;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $stats = [
            'total_clients' => Client::count(),
            'premium_clients' => Client::whereIn('plan_type', ['premium', 'enterprise'])->count(),
            'total_orders' => Order::count(),
            'total_revenue' => UpgradeRequest::where('status', 'approved')->sum('amount'),
            'new_clients_today' => Client::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'active_services' => Service::whereIn('status', ['confirmed', 'in_progress'])->count(),
            'facebook_pages' => FacebookPage::where('is_connected', true)->count(),
        ];

        $recent_clients = Client::latest()->limit(5)->get();
        
        $recent_activities = $this->getRecentActivities();

        return view('admin.dashboard', compact('stats', 'recent_clients', 'recent_activities'));
    }

    private function getRecentActivities()
    {
        $activities = [];

        // Get recent client registrations
        $recent_clients = Client::latest()->limit(3)->get();
        foreach ($recent_clients as $client) {
            $activities[] = [
                'icon' => 'user-plus',
                'message' => "New client registered: {$client->name}",
                'time' => $client->created_at->diffForHumans(),
            ];
        }

        // Get recent orders
        $recent_orders = Order::latest()->limit(3)->get();
        foreach ($recent_orders as $order) {
            $activities[] = [
                'icon' => 'shopping-cart',
                'message' => "New order #{$order->order_number} - {$order->product_name}",
                'time' => $order->created_at->diffForHumans(),
            ];
        }

        // Sort by time and limit to 8 activities
        usort($activities, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        return array_slice($activities, 0, 8);
    }
}
