@extends('layouts.admin')

@section('title', 'Upgrade Requests')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Upgrade Requests Management</h3>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0">Total Requests</p>
                                </div>
                                <i class="fas fa-list fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                    <p class="mb-0">Pending</p>
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
                                    <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                                    <p class="mb-0">Approved</p>
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
                                    <h4 class="mb-0">{{ $stats['rejected'] }}</h4>
                                    <p class="mb-0">Rejected</p>
                                </div>
                                <i class="fas fa-times-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.upgrade-requests.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Actions -->
            @if($stats['pending'] > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Bulk Actions</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.upgrade-requests.bulk-action') }}" id="bulkActionForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <select name="action" class="form-control" required>
                                        <option value="">Select Action</option>
                                        <option value="approve">Approve</option>
                                        <option value="reject">Reject</option>
                                    </select>
                                </div>
                                <div class="col-md-2" id="monthsField" style="display: none;">
                                    <input type="number" name="subscription_months" class="form-control" placeholder="Months" min="1" max="12" value="1">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="admin_notes" class="form-control" placeholder="Admin Notes (optional)">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-warning" id="bulkSubmit" disabled>Process Selected</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Requests Table -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Upgrade Requests</h5>
                        @if($stats['pending'] > 0)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                                <label class="form-check-label" for="selectAll">Select All</label>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($upgradeRequests->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 text-muted">No upgrade requests found</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        @if($stats['pending'] > 0)
                                            <th width="50">
                                                <input type="checkbox" id="selectAllHeader">
                                            </th>
                                        @endif
                                        <th>Client</th>
                                        <th>Plan</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upgradeRequests as $request)
                                        <tr>
                                            @if($stats['pending'] > 0)
                                                <td>
                                                    @if($request->isPending())
                                                        <input type="checkbox" name="requests[]" value="{{ $request->id }}" class="request-checkbox" form="bulkActionForm">
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                <div>
                                                    <strong>{{ $request->client->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $request->client->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ ucfirst($request->current_plan) }}</span>
                                                <i class="fas fa-arrow-right mx-2"></i>
                                                <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $request->requested_plan)) }}</span>
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($request->amount) }}</strong>
                                            </td>
                                            <td>{{ $request->payment_method }}</td>
                                            <td>
                                                <div>
                                                    {{ $request->created_at->format('M d, Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $request->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $request->getStatusBadgeClass() }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.upgrade-requests.show', $request) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($upgradeRequests->hasPages())
                            <div class="card-footer">
                                {{ $upgradeRequests->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    const bulkSubmit = document.getElementById('bulkSubmit');
    const actionSelect = document.querySelector('select[name="action"]');
    const monthsField = document.getElementById('monthsField');

    // Handle select all functionality
    function updateSelectAll() {
        const checked = document.querySelectorAll('.request-checkbox:checked').length;
        const total = checkboxes.length;
        
        if (selectAll) {
            selectAll.checked = checked === total && total > 0;
            selectAll.indeterminate = checked > 0 && checked < total;
        }
        
        if (selectAllHeader) {
            selectAllHeader.checked = checked === total && total > 0;
            selectAllHeader.indeterminate = checked > 0 && checked < total;
        }
        
        bulkSubmit.disabled = checked === 0;
    }

    // Select all checkboxes
    [selectAll, selectAllHeader].forEach(checkbox => {
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectAll();
            });
        }
    });

    // Individual checkbox change
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectAll);
    });

    // Show/hide months field based on action
    if (actionSelect) {
        actionSelect.addEventListener('change', function() {
            if (this.value === 'approve') {
                monthsField.style.display = 'block';
                monthsField.querySelector('input').required = true;
            } else {
                monthsField.style.display = 'none';
                monthsField.querySelector('input').required = false;
            }
        });
    }

    // Initial update
    updateSelectAll();
});
</script>
@endpush