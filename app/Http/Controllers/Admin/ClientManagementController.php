<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['orders', 'customers', 'facebookPages']);
        
        // Filter by search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        // Filter by plan type
        if ($request->has('plan') && $request->plan) {
            $query->where('plan_type', $request->plan);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $clients = $query->latest()->paginate(15);
        
        // Get stats
        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'premium_clients' => Client::where('plan_type', 'premium')->count(),
            'free_clients' => Client::where('plan_type', 'free')->count(),
        ];
        
        return view('admin.clients.index', compact('clients', 'stats'));
    }
    
    public function premium()
    {
        $clients = Client::where('plan_type', 'premium')
            ->with(['orders', 'customers', 'facebookPages'])
            ->latest()
            ->paginate(15);
            
        // Calculate revenue from orders table
        $premiumClientIds = Client::where('plan_type', 'premium')->pluck('id');
        $totalRevenue = \App\Models\Order::whereIn('client_id', $premiumClientIds)
            ->where('status', 'delivered')
            ->sum('total_amount') ?? 0;
        $avgRevenue = $premiumClientIds->count() > 0 ? $totalRevenue / $premiumClientIds->count() : 0;
        
        $stats = [
            'premium_clients' => $clients->total(),
            'total_revenue' => $totalRevenue,
            'avg_revenue' => $avgRevenue,
        ];
        
        return view('admin.clients.premium', compact('clients', 'stats'));
    }
    
    public function create()
    {
        return view('admin.clients.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'plan_type' => 'required|in:free,premium,enterprise',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        $client = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'plan_type' => $request->plan_type,
            'status' => $request->status,
            'settings' => [
                'language' => 'en',
                'notifications' => true,
                'email_alerts' => true,
            ],
        ]);
        
        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.client_created_successfully'));
    }
    
    public function show(Client $client)
    {
        $client->load([
            'orders' => function($query) {
                $query->latest()->limit(10);
            },
            'customers' => function($query) {
                $query->latest()->limit(10);
            },
            'facebookPages'
        ]);
        
        $stats = [
            'total_orders' => $client->orders()->count(),
            'total_customers' => $client->customers()->count(),
            'facebook_pages' => $client->facebookPages()->count(),
            'revenue' => $client->orders()->sum('total_amount') ?? 0,
        ];
        
        return view('admin.clients.show', compact('client', 'stats'));
    }
    
    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }
    
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'plan_type' => 'required|in:free,premium,enterprise',
            'status' => 'required|in:active,inactive,suspended',
        ]);
        
        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'plan_type' => $request->plan_type,
            'status' => $request->status,
        ];
        
        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $updateData['password'] = Hash::make($request->password);
        }
        
        $client->update($updateData);
        
        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.client_updated_successfully'));
    }
    
    public function destroy(Client $client)
    {
        $client->delete();
        
        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.client_deleted_successfully'));
    }
}