@extends('layouts.client')

@section('title', __('common.settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <!-- Account Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.account_settings') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.settings.update') }}">
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
                                <i class="fas fa-save"></i> {{ __('client.save_account_settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Change -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.change_password') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.password.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="current_password">{{ __('client.current_password') }}</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password">{{ __('client.new_password') }}</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password_confirmation">{{ __('client.confirm_password') }}</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-lock"></i> {{ __('client.update_password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.notification_settings') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.notifications.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>{{ __('client.email_notifications') }}</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_new_orders" name="email_notifications[]" value="new_orders" 
                                           {{ in_array('new_orders', auth('client')->user()->settings['email_notifications'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_new_orders">
                                        {{ __('client.new_orders') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_new_customers" name="email_notifications[]" value="new_customers"
                                           {{ in_array('new_customers', auth('client')->user()->settings['email_notifications'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_new_customers">
                                        {{ __('client.new_customers') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_service_reminders" name="email_notifications[]" value="service_reminders"
                                           {{ in_array('service_reminders', auth('client')->user()->settings['email_notifications'] ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_service_reminders">
                                        {{ __('client.service_reminders') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>{{ __('client.system_notifications') }}</h6>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="browser_notifications" name="browser_notifications" value="1"
                                           {{ auth('client')->user()->settings['browser_notifications'] ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label" for="browser_notifications">
                                        {{ __('client.browser_notifications') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="sound_notifications" name="sound_notifications" value="1"
                                           {{ auth('client')->user()->settings['sound_notifications'] ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sound_notifications">
                                        {{ __('client.sound_notifications') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-bell"></i> {{ __('client.save_notification_settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Business Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.business_settings') }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.business.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="business_type">{{ __('client.business_type') }}</label>
                                    <select class="form-control" id="business_type" name="business_type">
                                        <option value="">{{ __('common.select') }}</option>
                                        <option value="ecommerce" {{ (auth('client')->user()->profile_data['business_type'] ?? '') === 'ecommerce' ? 'selected' : '' }}>{{ __('client.ecommerce') }}</option>
                                        <option value="service" {{ (auth('client')->user()->profile_data['business_type'] ?? '') === 'service' ? 'selected' : '' }}>{{ __('client.service_business') }}</option>
                                        <option value="both" {{ (auth('client')->user()->profile_data['business_type'] ?? '') === 'both' ? 'selected' : '' }}>{{ __('client.both') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="currency">{{ __('client.currency') }}</label>
                                    <select class="form-control" id="currency" name="currency">
                                        <option value="BDT" {{ (auth('client')->user()->settings['currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>৳ BDT</option>
                                        <option value="USD" {{ (auth('client')->user()->settings['currency'] ?? 'BDT') === 'USD' ? 'selected' : '' }}>$ USD</option>
                                        <option value="EUR" {{ (auth('client')->user()->settings['currency'] ?? 'BDT') === 'EUR' ? 'selected' : '' }}>€ EUR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="timezone">{{ __('client.timezone') }}</label>
                                    <select class="form-control" id="timezone" name="timezone">
                                        <option value="Asia/Dhaka" {{ (auth('client')->user()->settings['timezone'] ?? 'Asia/Dhaka') === 'Asia/Dhaka' ? 'selected' : '' }}>Asia/Dhaka</option>
                                        <option value="UTC" {{ (auth('client')->user()->settings['timezone'] ?? 'Asia/Dhaka') === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="language">{{ __('client.default_language') }}</label>
                                    <select class="form-control" id="language" name="language">
                                        <option value="bn" {{ (auth('client')->user()->settings['language'] ?? 'bn') === 'bn' ? 'selected' : '' }}>🇧🇩 বাংলা</option>
                                        <option value="en" {{ (auth('client')->user()->settings['language'] ?? 'bn') === 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-building"></i> {{ __('client.save_business_settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Plan Information -->
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
                        <div class="alert alert-warning">
                            <small>
                                <strong>{{ __('client.trial') }}:</strong> {{ max(0, 10 - floor(auth('client')->user()->created_at->diffInDays(now()))) }} {{ __('client.days_left') }}
                            </small>
                        </div>
                        <a href="{{ route('client.upgrade.create') }}" class="btn btn-warning btn-sm w-100 mb-2">{{ __('client.upgrade_now') }}</a>
                        <small class="text-muted d-block text-center">{{ __('client.upgrade_to_unlock_features') }}</small>
                    @else
                        <div class="alert alert-success">
                            <small>
                                <strong>Status:</strong> Active<br>
                                <strong>Expires:</strong> {{ auth('client')->user()->subscription_expires_at ? auth('client')->user()->subscription_expires_at->format('M d, Y') : 'Never' }}
                            </small>
                        </div>
                        <a href="{{ route('client.upgrade.index') }}" class="btn btn-outline-primary btn-sm w-100">{{ __('client.manage_subscription') }}</a>
                    @endif
                </div>
            </div>

            <!-- Account Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">{{ __('client.account_actions') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> {{ __('client.export_data') }}
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-file-invoice"></i> {{ __('client.billing_history') }}
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-question-circle"></i> {{ __('client.support') }}
                        </a>
                        <hr>
                        <a href="#" class="btn btn-outline-danger btn-sm" onclick="confirmAccountDeletion()">
                            <i class="fas fa-trash"></i> {{ __('client.delete_account') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ __('client.recent_activity') }}</h5>
                </div>
                <div class="card-body">
                    <div class="activity-log">
                        <div class="activity-item mb-2">
                            <small class="text-muted">{{ now()->format('M d, H:i') }}</small>
                            <p class="mb-1">{{ __('client.logged_in_from_ip') }}: {{ request()->ip() }}</p>
                        </div>
                        <div class="activity-item mb-2">
                            <small class="text-muted">{{ auth('client')->user()->updated_at->format('M d, H:i') }}</small>
                            <p class="mb-1">{{ __('client.profile_updated') }}</p>
                        </div>
                        <div class="activity-item mb-2">
                            <small class="text-muted">{{ auth('client')->user()->created_at->format('M d, H:i') }}</small>
                            <p class="mb-1">{{ __('client.account_created') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmAccountDeletion() {
    if (confirm('{{ __('client.confirm_account_deletion') }}')) {
        if (confirm('{{ __('client.confirm_permanent_deletion') }}')) {
            // Redirect to account deletion
            window.location.href = '{{ route('client.account.delete') }}';
        }
    }
}

// Request browser notification permission
document.getElementById('browser_notifications').addEventListener('change', function() {
    if (this.checked) {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }
});
</script>
@endpush