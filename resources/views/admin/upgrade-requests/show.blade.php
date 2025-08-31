@extends('layouts.admin')

@section('title', 'Upgrade Request Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3>Upgrade Request #{{ $upgradeRequest->id }}</h3>
                    <p class="text-muted mb-0">
                        Submitted {{ $upgradeRequest->created_at->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>
                <a href="{{ route('admin.upgrade-requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Client Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Client Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>{{ $upgradeRequest->client->name }}</h6>
                                    <p class="text-muted mb-1">{{ $upgradeRequest->client->email }}</p>
                                    <p class="text-muted mb-1">{{ $upgradeRequest->client->phone }}</p>
                                    <small class="text-muted">
                                        Member since {{ $upgradeRequest->client->created_at->format('M d, Y') }}
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>Current Plan:</strong> 
                                        <span class="badge badge-info">{{ ucfirst($upgradeRequest->current_plan) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Pages:</strong> {{ $upgradeRequest->client->facebookPages()->count() }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Customers:</strong> {{ $upgradeRequest->client->customers()->count() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Request Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Requested Plan:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $upgradeRequest->requested_plan)) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Amount:</strong></div>
                                <div class="col-sm-8">
                                    <span class="h5 text-success">৳{{ number_format($upgradeRequest->amount) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Payment Method:</strong></div>
                                <div class="col-sm-8">{{ $upgradeRequest->payment_method }}</div>
                            </div>

                            @if($upgradeRequest->transaction_id)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Transaction ID:</strong></div>
                                    <div class="col-sm-8">
                                        <code>{{ $upgradeRequest->transaction_id }}</code>
                                    </div>
                                </div>
                            @endif

                            @if($upgradeRequest->notes)
                                <div class="row mb-3">
                                    <div class="col-sm-4"><strong>Client Notes:</strong></div>
                                    <div class="col-sm-8">
                                        <div class="alert alert-light">
                                            {{ $upgradeRequest->notes }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Proof -->
                    @if($upgradeRequest->payment_proof)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Payment Proof</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $upgradeRequest->payment_proof) }}" 
                                     alt="Payment Proof" 
                                     class="img-fluid" 
                                     style="max-height: 500px; border: 1px solid #ddd; border-radius: 8px;">
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $upgradeRequest->payment_proof) }}" 
                                       target="_blank" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> View Full Size
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Response -->
                    @if($upgradeRequest->admin_notes || $upgradeRequest->processed_at)
                        <div class="card">
                            <div class="card-header">
                                <h5>Admin Response</h5>
                            </div>
                            <div class="card-body">
                                @if($upgradeRequest->processed_at)
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Processed Date:</strong></div>
                                        <div class="col-sm-8">
                                            {{ $upgradeRequest->processed_at->format('M d, Y \a\t h:i A') }}
                                        </div>
                                    </div>
                                @endif

                                @if($upgradeRequest->processedBy)
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Processed By:</strong></div>
                                        <div class="col-sm-8">{{ $upgradeRequest->processedBy->name }}</div>
                                    </div>
                                @endif

                                @if($upgradeRequest->admin_notes)
                                    <div class="row mb-3">
                                        <div class="col-sm-4"><strong>Admin Notes:</strong></div>
                                        <div class="col-sm-8">
                                            <div class="alert alert-info">
                                                {{ $upgradeRequest->admin_notes }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <!-- Status Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Request Status</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($upgradeRequest->status === 'pending')
                                    <i class="fas fa-clock text-warning" style="font-size: 3rem;"></i>
                                @elseif($upgradeRequest->status === 'approved')
                                    <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
                                @endif
                            </div>
                            
                            <h4>
                                <span class="badge {{ $upgradeRequest->getStatusBadgeClass() }} p-2">
                                    {{ ucfirst($upgradeRequest->status) }}
                                </span>
                            </h4>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($upgradeRequest->isPending())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6>Actions</h6>
                            </div>
                            <div class="card-body">
                                <!-- Approve Form -->
                                <form method="POST" action="{{ route('admin.upgrade-requests.approve', $upgradeRequest) }}" class="mb-3">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label>Subscription Duration (months)</label>
                                        <select name="subscription_months" class="form-control" required>
                                            <option value="1">1 Month</option>
                                            <option value="3">3 Months</option>
                                            <option value="6">6 Months</option>
                                            <option value="12" selected>12 Months</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label>Admin Notes (Optional)</label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Enter any notes for the client..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-check"></i> Approve Request
                                    </button>
                                </form>

                                <hr>

                                <!-- Reject Form -->
                                <form method="POST" action="{{ route('admin.upgrade-requests.reject', $upgradeRequest) }}">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label>Rejection Reason <span class="text-danger">*</span></label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Please explain why this request is being rejected..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times"></i> Reject Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Client Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h6>Client Activity</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Upgrade Request Submitted</h6>
                                        <p class="timeline-description">{{ $upgradeRequest->created_at->format('M d, Y \a\t h:i A') }}</p>
                                    </div>
                                </div>
                                
                                @if($upgradeRequest->processed_at)
                                    <div class="timeline-item">
                                        <div class="timeline-marker {{ $upgradeRequest->isApproved() ? 'bg-success' : 'bg-danger' }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">Request {{ ucfirst($upgradeRequest->status) }}</h6>
                                            <p class="timeline-description">{{ $upgradeRequest->processed_at->format('M d, Y \a\t h:i A') }}</p>
                                        </div>
                                    </div>
                                @endif

                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Account Created</h6>
                                        <p class="timeline-description">{{ $upgradeRequest->client->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -16px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding-left: 20px;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-description {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
}
</style>
@endpush