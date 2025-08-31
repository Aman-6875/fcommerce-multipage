@extends('layouts.admin')

@section('title', 'Payment Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Payment Settings</h3>
            </div>

            <form method="POST" action="{{ route('admin.settings.payments.update') }}">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">General Payment Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="payment_currency" class="form-label">Default Currency</label>
                            <select class="form-control" id="payment_currency" name="payment_currency" required>
                                <option value="BDT" {{ old('payment_currency', $settings['payment_currency'] ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (Bangladeshi Taka)</option>
                                <option value="USD" {{ old('payment_currency', $settings['payment_currency'] ?? '') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                                <option value="EUR" {{ old('payment_currency', $settings['payment_currency'] ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">bKash Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bkash_app_key" class="form-label">App Key</label>
                                    <input type="text" class="form-control" id="bkash_app_key" name="bkash_app_key" 
                                           value="{{ old('bkash_app_key', $settings['bkash_app_key'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bkash_app_secret" class="form-label">App Secret</label>
                                    <input type="password" class="form-control" id="bkash_app_secret" name="bkash_app_secret" 
                                           value="{{ old('bkash_app_secret', $settings['bkash_app_secret'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bkash_username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="bkash_username" name="bkash_username" 
                                           value="{{ old('bkash_username', $settings['bkash_username'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bkash_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="bkash_password" name="bkash_password" 
                                           value="{{ old('bkash_password', $settings['bkash_password'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="bkash_sandbox_mode" name="bkash_sandbox_mode" value="1"
                                           {{ old('bkash_sandbox_mode', $settings['bkash_sandbox_mode'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="bkash_sandbox_mode">
                                        Enable Sandbox Mode
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Nagad Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nagad_merchant_id" class="form-label">Merchant ID</label>
                                    <input type="text" class="form-control" id="nagad_merchant_id" name="nagad_merchant_id" 
                                           value="{{ old('nagad_merchant_id', $settings['nagad_merchant_id'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="nagad_merchant_private_key" class="form-label">Merchant Private Key</label>
                                    <textarea class="form-control" id="nagad_merchant_private_key" name="nagad_merchant_private_key" rows="4">{{ old('nagad_merchant_private_key', $settings['nagad_merchant_private_key'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="nagad_sandbox_mode" name="nagad_sandbox_mode" value="1"
                                           {{ old('nagad_sandbox_mode', $settings['nagad_sandbox_mode'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="nagad_sandbox_mode">
                                        Enable Sandbox Mode
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Stripe Configuration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stripe_publishable_key" class="form-label">Publishable Key</label>
                                    <input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" 
                                           value="{{ old('stripe_publishable_key', $settings['stripe_publishable_key'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stripe_secret_key" class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" id="stripe_secret_key" name="stripe_secret_key" 
                                           value="{{ old('stripe_secret_key', $settings['stripe_secret_key'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="stripe_webhook_secret" class="form-label">Webhook Secret</label>
                                    <input type="password" class="form-control" id="stripe_webhook_secret" name="stripe_webhook_secret" 
                                           value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Payment Settings</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection