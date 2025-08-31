@extends('layouts.client')

@section('title', __('common.profile'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('common.profile') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.profile') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name">{{ __('client.full_name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="{{ auth('client')->user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">{{ __('client.email_address') }}</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ auth('client')->user()->email }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone">{{ __('client.phone_number') }}</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="{{ auth('client')->user()->phone }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="business_name">{{ __('client.business_name') }}</label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" 
                                           value="{{ auth('client')->user()->profile_data['business_name'] ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('common.save_changes') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Plan Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">{{ __('client.current_plan') }}</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if(auth('client')->user()->isPremium())
                            <i class="fas fa-crown text-warning" style="font-size: 2rem;"></i>
                        @else
                            <i class="fas fa-rocket text-info" style="font-size: 2rem;"></i>
                        @endif
                    </div>
                    
                    <h6 class="text-center">{{ ucfirst(auth('client')->user()->plan_type) }} Plan</h6>
                    
                    @if(auth('client')->user()->isFree())
                        <div class="alert alert-info">
                            <small>
                                <strong>{{ __('client.trial') }}:</strong> {{ max(0, 10 - floor(auth('client')->user()->created_at->diffInDays(now()))) }} {{ __('client.days_left') }}<br>
                                <strong>{{ __('client.facebook_pages') }}:</strong> {{ auth('client')->user()->facebookPages()->count() }}/{{ auth('client')->user()->getFacebookPageLimit() }}<br>
                                <strong>{{ __('client.customers') }}:</strong> {{ auth('client')->user()->customers()->count() }}/20<br>
                                <strong>{{ __('client.messages') }}:</strong> {{ auth('client')->user()->customers()->sum('interaction_count') }}/50
                            </small>
                        </div>
                        <a href="#" class="btn btn-warning btn-sm w-100">{{ __('client.upgrade_now') }}</a>
                    @else
                        <div class="alert alert-success">
                            <small>
                                <strong>{{ __('common.status') }}:</strong> {{ __('common.active') }}<br>
                                <strong>{{ __('client.expires') }}:</strong> {{ auth('client')->user()->subscription_expires_at ? auth('client')->user()->subscription_expires_at->format('M d, Y') : __('common.never') }}<br>
                                <strong>{{ __('client.facebook_pages') }}:</strong> {{ auth('client')->user()->facebookPages()->count() }}@if(auth('client')->user()->plan_type !== 'enterprise')/{{ auth('client')->user()->getFacebookPageLimit() }}@endif
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ __('common.statistics') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-primary">{{ auth('client')->user()->facebookPages()->count() }}</h4>
                                <small class="text-muted">{{ __('client.facebook_pages') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-info">{{ auth('client')->user()->customers()->count() }}</h4>
                                <small class="text-muted">{{ __('client.customers') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-success">{{ auth('client')->user()->orders()->count() }}</h4>
                                <small class="text-muted">{{ __('client.orders') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <h4 class="text-warning">{{ auth('client')->user()->services()->count() }}</h4>
                                <small class="text-muted">{{ __('client.services') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection