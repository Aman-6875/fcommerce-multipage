@extends('layouts.admin')

@section('title', 'Revenue Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Revenue Report</h3>
                <div>
                    <form method="GET" class="d-inline-flex gap-2">
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" style="width: auto;">
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" style="width: auto;">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">৳{{ number_format($totalRevenue) }}</h4>
                                    <p class="mb-0">Total Revenue</p>
                                </div>
                                <i class="fas fa-chart-line fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($totalUpgrades) }}</h4>
                                    <p class="mb-0">Total Upgrades</p>
                                </div>
                                <i class="fas fa-arrow-up fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">৳{{ number_format($avgUpgradeValue) }}</h4>
                                    <p class="mb-0">Avg Upgrade Value</p>
                                </div>
                                <i class="fas fa-calculator fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">
                                        @php
                                            $growthRate = 0;
                                            if($monthlyComparison['previous_month'] > 0) {
                                                $growthRate = (($monthlyComparison['current_month'] - $monthlyComparison['previous_month']) / $monthlyComparison['previous_month']) * 100;
                                            }
                                        @endphp
                                        {{ number_format($growthRate, 1) }}%
                                    </h4>
                                    <p class="mb-0">Growth Rate</p>
                                </div>
                                <i class="fas fa-trending-up fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Revenue Chart -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Daily Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>

            <!-- Top Clients -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Performing Clients</h5>
                </div>
                <div class="card-body p-0">
                    @if($topClients->isEmpty())
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
                                        <th>Total Revenue</th>
                                        <th>Upgrades Count</th>
                                        <th>Avg Upgrade Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topClients as $client)
                                        <tr>
                                            <td>
                                                <strong>{{ $client->client->name }}</strong><br>
                                                <small class="text-muted">{{ $client->client->email }}</small>
                                            </td>
                                            <td>৳{{ number_format($client->revenue) }}</td>
                                            <td>{{ $client->upgrade_count }}</td>
                                            <td>৳{{ number_format($client->revenue / $client->upgrade_count) }}</td>
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
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueData = @json($revenueData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => item.date),
        datasets: [{
            label: 'Revenue',
            data: revenueData.map(item => item.revenue),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1,
            fill: true
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
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '৳' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush