<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['client', 'customer'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id') && $request->client_id !== '') {
            $query->where('client_id', $request->client_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(20);
        
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $clients = Client::orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'stats', 'clients'));
    }

    public function pending()
    {
        $orders = Order::with(['client', 'customer'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Order::where('status', 'pending')->count(),
        ];

        return view('admin.orders.pending', compact('orders', 'stats'));
    }

    public function delivered()
    {
        $orders = Order::with(['client', 'customer'])
            ->where('status', 'delivered')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('status', 'delivered')->sum('total_amount'),
        ];

        return view('admin.orders.delivered', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['client', 'customer', 'orderItems']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}