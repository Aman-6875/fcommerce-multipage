@extends('layouts.admin')

@section('title', 'Order Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Order Report</h3>
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
                                    <h4 class="mb-0">{{ number_format($stats['total_orders']) }}</h4>
                                    <p class="mb-0">Total Orders</p>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['pending_orders']) }}</h4>
                                    <p class="mb-0">Pending Orders</p>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['delivered_orders']) }}</h4>
                                    <p class="mb-0">Delivered Orders</p>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ number_format($stats['cancelled_orders']) }}</h4>
                                    <p class="mb-0">Cancelled Orders</p>
                                </div>
                                <i class="fas fa-times-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Daily Orders Chart -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Daily Orders & Revenue</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="dailyOrdersChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Order Status Distribution -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top Selling Products</h5>
                </div>
                <div class="card-body p-0">
                    @if($topProducts->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-box text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 text-muted">No product data available</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Orders Count</th>
                                        <th>Total Revenue</th>
                                        <th>Avg Order Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts as $product)
                                        <tr>
                                            <td><strong>{{ $product->product_name }}</strong></td>
                                            <td>{{ $product->orders_count }}</td>
                                            <td>৳{{ number_format($product->revenue) }}</td>
                                            <td>৳{{ number_format($product->revenue / $product->orders_count) }}</td>
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
// Daily Orders Chart
const dailyCtx = document.getElementById('dailyOrdersChart').getContext('2d');
const dailyOrders = @json($dailyOrders);

new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: dailyOrders.map(item => item.date),
        datasets: [{
            label: 'Orders',
            data: dailyOrders.map(item => item.orders_count),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            yAxisID: 'y'
        }, {
            label: 'Revenue',
            data: dailyOrders.map(item => item.total_amount),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false,
                },
            },
        }
    }
});

// Order Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const ordersByStatus = @json($ordersByStatus);

new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ordersByStatus.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
        datasets: [{
            data: ordersByStatus.map(item => item.count),
            backgroundColor: [
                'rgba(255, 206, 84, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(153, 102, 255, 0.8)'
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