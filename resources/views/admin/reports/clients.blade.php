@extends('layouts.admin')

@section('title', 'Client Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Client Report</h3>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['total_clients']) }}</h4>
                                    <p class="mb-0">Total Clients</p>
                                </div>
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['active_clients']) }}</h4>
                                    <p class="mb-0">Active Clients</p>
                                </div>
                                <i class="fas fa-user-check fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['premium_clients']) }}</h4>
                                    <p class="mb-0">Premium Clients</p>
                                </div>
                                <i class="fas fa-crown fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['free_clients']) }}</h4>
                                    <p class="mb-0">Free Clients</p>
                                </div>
                                <i class="fas fa-user fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Client Growth Chart -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Client Growth Over Time</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="growthChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Plan Distribution -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Plan Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="planChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Clients by Revenue -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Clients by Revenue</h5>
                </div>
                <div class="card-body p-0">
                    @if($topClientsByRevenue->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 text-muted">No revenue data available</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Client</th>
                                        <th>Plan</th>
                                        <th>Total Revenue</th>
                                        <th>Total Orders</th>
                                        <th>Avg Order Value</th>
                                        <th>Join Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topClientsByRevenue as $client)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $client->name }}</strong><br>
                                                    <small class="text-muted">{{ $client->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $client->plan_type === 'premium' ? 'warning' : ($client->plan_type === 'enterprise' ? 'danger' : 'secondary') }}">
                                                    {{ ucfirst($client->plan_type) }}
                                                </span>
                                            </td>
                                            <td>৳{{ number_format($client->total_revenue ?? 0) }}</td>
                                            <td>{{ $client->total_orders ?? 0 }}</td>
                                            <td>
                                                @if($client->total_orders > 0 && $client->total_revenue > 0)
                                                    ৳{{ number_format($client->total_revenue / $client->total_orders) }}
                                                @else
                                                    ৳0
                                                @endif
                                            </td>
                                            <td>{{ $client->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Client Growth Chart
const growthCtx = document.getElementById('growthChart').getContext('2d');
const clientGrowth = @json($clientGrowth);

new Chart(growthCtx, {
    type: 'bar',
    data: {
        labels: clientGrowth.map(item => item.month),
        datasets: [{
            label: 'New Clients',
            data: clientGrowth.map(item => item.count),
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Plan Distribution Chart
const planCtx = document.getElementById('planChart').getContext('2d');
const planDistribution = @json($planDistribution);

new Chart(planCtx, {
    type: 'doughnut',
    data: {
        labels: planDistribution.map(item => item.plan_type.charAt(0).toUpperCase() + item.plan_type.slice(1)),
        datasets: [{
            data: planDistribution.map(item => item.count),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endpush