@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>General Settings</h3>
            </div>

            <form method="POST" action="{{ route('admin.settings.general.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Application Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="app_name" class="form-label">Application Name</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name" 
                                           value="{{ old('app_name', $settings['app_name'] ?? 'FCommerce') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                           value="{{ old('admin_email', $settings['admin_email'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="app_description" class="form-label">Application Description</label>
                                    <textarea class="form-control" id="app_description" name="app_description" rows="3">{{ old('app_description', $settings['app_description'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Company Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_phone" class="form-label">Company Phone</label>
                                    <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="company_address" class="form-label">Company Address</label>
                                    <textarea class="form-control" id="company_address" name="company_address" rows="3">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Facebook Integration</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_app_id" class="form-label">Facebook App ID</label>
                                    <input type="text" class="form-control" id="facebook_app_id" name="facebook_app_id" 
                                           value="{{ old('facebook_app_id', $settings['facebook_app_id'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_app_secret" class="form-label">Facebook App Secret</label>
                                    <input type="password" class="form-control" id="facebook_app_secret" name="facebook_app_secret" 
                                           value="{{ old('facebook_app_secret', $settings['facebook_app_secret'] ?? '') }}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="facebook_webhook_verify_token" class="form-label">Webhook Verify Token</label>
                                    <input type="text" class="form-control" id="facebook_webhook_verify_token" name="facebook_webhook_verify_token" 
                                           value="{{ old('facebook_webhook_verify_token', $settings['facebook_webhook_verify_token'] ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Currency Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency_code" class="form-label">Currency Code</label>
                                    <select class="form-control" id="currency_code" name="currency_code" required>
                                        <option value="BDT" {{ old('currency_code', $settings['currency_code'] ?? 'BDT') == 'BDT' ? 'selected' : '' }}>BDT - Bangladeshi Taka</option>
                                        <option value="USD" {{ old('currency_code', $settings['currency_code'] ?? 'BDT') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="EUR" {{ old('currency_code', $settings['currency_code'] ?? 'BDT') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                        <option value="GBP" {{ old('currency_code', $settings['currency_code'] ?? 'BDT') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                        <option value="INR" {{ old('currency_code', $settings['currency_code'] ?? 'BDT') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                    <input type="text" class="form-control" id="currency_symbol" name="currency_symbol" 
                                           value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '৳') }}" 
                                           placeholder="৳" maxlength="10" required>
                                    <small class="text-muted">Enter the currency symbol (e.g., ৳, $, €, £, ₹)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">File Uploads</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="app_logo" class="form-label">Application Logo</label>
                                    <input type="file" class="form-control" id="app_logo" name="app_logo" accept="image/*">
                                    @if(isset($settings['app_logo']) && $settings['app_logo'])
                                        <small class="text-muted">Current: {{ basename($settings['app_logo']) }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="app_favicon" class="form-label">Application Favicon</label>
                                    <input type="file" class="form-control" id="app_favicon" name="app_favicon" accept="image/*">
                                    @if(isset($settings['app_favicon']) && $settings['app_favicon'])
                                        <small class="text-muted">Current: {{ basename($settings['app_favicon']) }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Settings</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection