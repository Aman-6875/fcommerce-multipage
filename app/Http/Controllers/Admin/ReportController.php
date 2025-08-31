<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Client;
use App\Models\Customer;
use App\Models\Service;
use App\Models\UpgradeRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $revenueData = UpgradeRequest::where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue, COUNT(*) as upgrade_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $revenueData->sum('revenue');
        $totalUpgrades = $revenueData->sum('upgrade_count');
        $avgUpgradeValue = $totalUpgrades > 0 ? $totalRevenue / $totalUpgrades : 0;

        $topClients = UpgradeRequest::where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('client')
            ->selectRaw('client_id, SUM(amount) as revenue, COUNT(*) as upgrade_count')
            ->groupBy('client_id')
            ->orderBy('revenue', 'desc')
            ->limit(10)
            ->get();

        $monthlyComparison = [
            'current_month' => UpgradeRequest::where('status', 'approved')
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->sum('amount'),
            'previous_month' => UpgradeRequest::where('status', 'approved')
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->whereYear('created_at', Carbon::now()->subMonth()->year)
                ->sum('amount'),
        ];

        return view('admin.reports.revenue', compact(
            'revenueData', 
            'totalRevenue', 
            'totalUpgrades', 
            'avgUpgradeValue', 
            'topClients',
            'monthlyComparison',
            'startDate', 
            'endDate'
        ));
    }

    public function clients(Request $request)
    {
        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::where('status', 'active')->count(),
            'premium_clients' => Client::whereIn('plan_type', ['premium', 'enterprise'])->count(),
            'free_clients' => Client::where('plan_type', 'free')->count(),
        ];

        $clientGrowth = Client::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        $topClientsByRevenue = Client::withSum(['orders as total_revenue' => function ($query) {
                $query->where('status', 'delivered');
            }], 'total_amount')
            ->withCount(['orders as total_orders'])
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();

        $planDistribution = Client::selectRaw('plan_type, COUNT(*) as count')
            ->groupBy('plan_type')
            ->get();

        return view('admin.reports.clients', compact(
            'stats', 
            'clientGrowth', 
            'topClientsByRevenue', 
            'planDistribution'
        ));
    }

    public function orders(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $stats = [
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])->count(),
            'pending_orders' => Order::where('status', 'pending')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $ordersByStatus = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $dailyOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders_count, SUM(total_amount) as total_amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topProducts = Order::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('product_name, COUNT(*) as orders_count, SUM(total_amount) as revenue')
            ->groupBy('product_name')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.reports.orders', compact(
            'stats', 
            'ordersByStatus', 
            'dailyOrders', 
            'topProducts',
            'startDate', 
            'endDate'
        ));
    }
}