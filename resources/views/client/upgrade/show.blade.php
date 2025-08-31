@extends('layouts.client')

@section('title', __('client.upgrade_request_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3>{{ __('client.upgrade_request_details') }}</h3>
                    <p class="text-muted mb-0">{{ __('client.request_id') }}: #{{ $upgradeRequest->id }}</p>
                </div>
                <a href="{{ route('client.upgrade.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Request Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>{{ __('client.request_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('client.request_date') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $upgradeRequest->created_at->format('M d, Y \a\t h:i A') }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('client.current_plan') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="badge badge-info">{{ ucfirst($upgradeRequest->current_plan) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('client.requested_plan') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $upgradeRequest->requested_plan)) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('client.amount') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="h5 text-success">৳{{ number_format($upgradeRequest->amount) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>{{ __('client.payment_method') }}:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $upgradeRequest->payment_method }}
                                </div>
                            </div>

                            @if($upgradeRequest->transaction_id)
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>{{ __('client.transaction_id') }}:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <code>{{ $upgradeRequest->transaction_id }}</code>
                                    </div>
                                </div>
                            @endif

                            @if($upgradeRequest->notes)
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>{{ __('client.notes') }}:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <p class="mb-0">{{ $upgradeRequest->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Proof -->
                    @if($upgradeRequest->payment_proof)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>{{ __('client.payment_proof') }}</h5>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ asset('storage/' . $upgradeRequest->payment_proof) }}" 
                                     alt="Payment Proof" 
                                     class="img-fluid" 
                                     style="max-height: 400px; border: 1px solid #ddd; border-radius: 8px;">
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $upgradeRequest->payment_proof) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> {{ __('client.view_full_size') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Admin Response -->
                    @if($upgradeRequest->admin_notes || $upgradeRequest->processed_at)
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ __('client.admin_response') }}</h5>
                            </div>
                            <div class="card-body">
                                @if($upgradeRequest->processed_at)
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ __('client.processed_date') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $upgradeRequest->processed_at->format('M d, Y \a\t h:i A') }}
                                        </div>
                                    </div>
                                @endif

                                @if($upgradeRequest->admin_notes)
                                    <div class="row mb-3">
                                        <div class="col-sm-4">
                                            <strong>{{ __('client.admin_notes') }}:</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="alert alert-info mb-0">
                                                <p class="mb-0">{{ $upgradeRequest->admin_notes }}</p>
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
                            <h5>{{ __('client.request_status') }}</h5>
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

                            @if($upgradeRequest->status === 'pending')
                                <p class="text-muted mt-3">
                                    {{ __('client.request_under_review') }}
                                </p>
                            @elseif($upgradeRequest->status === 'approved')
                                <p class="text-success mt-3">
                                    {{ __('client.request_approved_message') }}
                                </p>
                            @else
                                <p class="text-danger mt-3">
                                    {{ __('client.request_rejected_message') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card">
                        <div class="card-header">
                            <h6>{{ __('client.next_steps') }}</h6>
                        </div>
                        <div class="card-body">
                            @if($upgradeRequest->status === 'pending')
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-hourglass-half text-warning me-2"></i>
                                        {{ __('client.wait_for_admin_review') }}
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-envelope text-info me-2"></i>
                                        {{ __('client.notification_will_be_sent') }}
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-question-circle text-muted me-2"></i>
                                        {{ __('client.contact_support_if_needed') }}
                                    </li>
                                </ul>
                            @elseif($upgradeRequest->status === 'approved')
                                <div class="alert alert-success">
                                    <h6>{{ __('client.congratulations') }}!</h6>
                                    <p class="mb-0">{{ __('client.account_upgraded_successfully') }}</p>
                                </div>
                                <a href="{{ route('client.dashboard') }}" class="btn btn-success w-100">
                                    <i class="fas fa-tachometer-alt"></i> {{ __('client.go_to_dashboard') }}
                                </a>
                            @else
                                <div class="alert alert-warning">
                                    <p class="mb-2">{{ __('client.request_rejected_info') }}</p>
                                    <p class="mb-0"><strong>{{ __('client.you_can') }}:</strong></p>
                                </div>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-redo text-info me-2"></i>
                                        {{ __('client.submit_new_request') }}
                                    </li>
                                    <li class="mb-0">
                                        <i class="fas fa-phone text-info me-2"></i>
                                        {{ __('client.contact_support') }}
                                    </li>
                                </ul>
                                @if(auth('client')->user()->upgradeRequests()->where('status', 'pending')->count() === 0)
                                    <a href="{{ route('client.upgrade.create') }}" class="btn btn-warning w-100 mt-2">
                                        <i class="fas fa-plus"></i> {{ __('client.create_new_request') }}
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection