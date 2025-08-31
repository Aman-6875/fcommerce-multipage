@extends('layouts.client')

@section('title', __('client.upgrade_subscription'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>{{ __('client.upgrade_subscription') }}</h3>
                @if(auth('client')->user()->isFree())
                    <a href="{{ route('client.upgrade.create') }}" class="btn btn-warning">
                        <i class="fas fa-rocket"></i> {{ __('client.upgrade_now') }}
                    </a>
                @endif
            </div>

            <!-- Current Plan Status -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('client.current_plan') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                @if(auth('client')->user()->isPremium())
                                    <i class="fas fa-crown text-warning me-2" style="font-size: 2rem;"></i>
                                @else
                                    <i class="fas fa-rocket text-info me-2" style="font-size: 2rem;"></i>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ ucfirst(auth('client')->user()->plan_type) }} Plan</h6>
                                    @if(auth('client')->user()->isFree())
                                        <small class="text-muted">
                                            {{ __('client.trial') }}: {{ auth('client')->user()->getTrialDaysRemaining() }} {{ __('client.days_left') }}
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            {{ __('client.expires') }}: {{ auth('client')->user()->subscription_expires_at ? auth('client')->user()->subscription_expires_at->format('M d, Y') : __('common.never') }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ __('client.plan_usage') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small>{{ __('client.facebook_pages') }}: {{ auth('client')->user()->facebookPages()->count() }}/{{ auth('client')->user()->getFacebookPageLimit() }}</small>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" style="width: {{ (auth('client')->user()->facebookPages()->count() / auth('client')->user()->getFacebookPageLimit()) * 100 }}%"></div>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small>{{ __('client.customers') }}: {{ auth('client')->user()->customers()->count() }}/{{ auth('client')->user()->isFree() ? '20' : '∞' }}</small>
                                @if(auth('client')->user()->isFree())
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" style="width: {{ (auth('client')->user()->customers()->count() / 20) * 100 }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade Requests -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('client.upgrade_requests') }}</h5>
                </div>
                <div class="card-body">
                    @if($currentRequests->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 text-muted">{{ __('client.no_upgrade_requests') }}</h6>
                            <p class="text-muted">{{ __('client.no_upgrade_requests_description') }}</p>
                            @if(auth('client')->user()->isFree())
                                <a href="{{ route('client.upgrade.create') }}" class="btn btn-warning">
                                    {{ __('client.create_upgrade_request') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('client.request_date') }}</th>
                                        <th>{{ __('client.plan') }}</th>
                                        <th>{{ __('client.amount') }}</th>
                                        <th>{{ __('client.payment_method') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentRequests as $request)
                                        <tr>
                                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $request->requested_plan)) }}</span>
                                            </td>
                                            <td>৳{{ number_format($request->amount) }}</td>
                                            <td>{{ $paymentMethods[$request->payment_method] ?? $request->payment_method }}</td>
                                            <td>
                                                <span class="badge {{ $request->getStatusBadgeClass() }}">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('client.upgrade.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                    {{ __('common.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Plans -->
            @if(auth('client')->user()->isFree())
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>{{ __('client.available_plans') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($plans as $key => $plan)
                                @if($key !== 'free')
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">{{ $plan['name'] }}</h5>
                                                <div class="pricing mb-3">
                                                    <span class="h4">৳{{ number_format($plan['monthly']) }}</span>
                                                    <small class="text-muted">/{{ __('client.month') }}</small>
                                                </div>
                                                <div class="pricing mb-3">
                                                    <span class="h5">৳{{ number_format($plan['yearly']) }}</span>
                                                    <small class="text-muted">/{{ __('client.year') }}</small>
                                                    <div class="badge badge-success">{{ __('client.save_16_percent') }}</div>
                                                </div>
                                                <div class="features mb-3">
                                                    @if($key === 'premium')
                                                        <ul class="list-unstyled text-sm">
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.up_to_5_pages') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.unlimited_customers') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.unlimited_messages') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.priority_support') }}</li>
                                                        </ul>
                                                    @else
                                                        <ul class="list-unstyled text-sm">
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.unlimited_pages') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.unlimited_customers') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.unlimited_messages') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.advanced_features') }}</li>
                                                            <li><i class="fas fa-check text-success"></i> {{ __('client.dedicated_support') }}</li>
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection