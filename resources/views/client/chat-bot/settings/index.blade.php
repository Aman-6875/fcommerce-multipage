@extends('layouts.client')

@section('title', __('client.business_settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('client.business_settings') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('common.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('client.business_settings') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('client.business_settings') }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('client.chat-bot.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="business_type" class="form-label">{{ __('client.business_type') }}</label>
                                <select class="form-select @error('business_type') is-invalid @enderror" id="business_type" name="business_type" required>
                                    <option value="">{{ __('client.choose_business_type') }}</option>
                                    <option value="software" {{ old('business_type', $config->business_type ?? '') === 'software' ? 'selected' : '' }}>{{ __('client.software_company') }}</option>
                                    <option value="restaurant" {{ old('business_type', $config->business_type ?? '') === 'restaurant' ? 'selected' : '' }}>{{ __('client.restaurant') }}</option>
                                    <option value="salon" {{ old('business_type', $config->business_type ?? '') === 'salon' ? 'selected' : '' }}>{{ __('client.salon_spa') }}</option>
                                    <option value="ecommerce" {{ old('business_type', $config->business_type ?? '') === 'ecommerce' ? 'selected' : '' }}>{{ __('client.ecommerce') }}</option>
                                    <option value="service" {{ old('business_type', $config->business_type ?? '') === 'service' ? 'selected' : '' }}>{{ __('client.service_business') }}</option>
                                    <option value="general" {{ old('business_type', $config->business_type ?? '') === 'general' ? 'selected' : '' }}>{{ __('client.general_business') }}</option>
                                </select>
                                @error('business_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="inquiry_type" class="form-label">{{ __('client.inquiry_type') }}</label>
                                <select class="form-select @error('inquiry_type') is-invalid @enderror" id="inquiry_type" name="inquiry_type" required>
                                    <option value="order" {{ old('inquiry_type', $config->inquiry_type ?? 'order') === 'order' ? 'selected' : '' }}>{{ __('client.orders') }}</option>
                                    <option value="booking" {{ old('inquiry_type', $config->inquiry_type ?? '') === 'booking' ? 'selected' : '' }}>{{ __('client.service_bookings') }}</option>
                                    <option value="appointment" {{ old('inquiry_type', $config->inquiry_type ?? '') === 'appointment' ? 'selected' : '' }}>{{ __('client.appointments') }}</option>
                                    <option value="consultation" {{ old('inquiry_type', $config->inquiry_type ?? '') === 'consultation' ? 'selected' : '' }}>{{ __('client.consultations') }}</option>
                                    <option value="inquiry" {{ old('inquiry_type', $config->inquiry_type ?? '') === 'inquiry' ? 'selected' : '' }}>{{ __('client.general_inquiries') }}</option>
                                </select>
                                @error('inquiry_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">{{ __('client.currency') }}</label>
                                <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                                    <option value="BDT" {{ old('currency', $config->currency ?? 'BDT') === 'BDT' ? 'selected' : '' }}>BDT (৳)</option>
                                    <option value="USD" {{ old('currency', $config->currency ?? '') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ old('currency', $config->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="GBP" {{ old('currency', $config->currency ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="default_language" class="form-label">{{ __('client.default_language') }}</label>
                                <select class="form-select @error('default_language') is-invalid @enderror" id="default_language" name="default_language">
                                    <option value="en" {{ old('default_language', $config->default_language ?? 'en') === 'en' ? 'selected' : '' }}>{{ __('client.english') }}</option>
                                    <option value="bn" {{ old('default_language', $config->default_language ?? '') === 'bn' ? 'selected' : '' }}>{{ __('client.bengali') }}</option>
                                </select>
                                @error('default_language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="company_name" class="form-label">{{ __('client.company_name') }}</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" value="{{ old('company_name', $config->company_name ?? '') }}" 
                                   placeholder="{{ __('client.company_name_placeholder') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_description_en" class="form-label">{{ __('client.company_description_english') }}</label>
                            <textarea class="form-control @error('company_description_en') is-invalid @enderror" 
                                      id="company_description_en" name="company_description_en" rows="3"
                                      placeholder="{{ __('client.company_description_placeholder') }}">{{ old('company_description_en', $config->company_description_en ?? '') }}</textarea>
                            @error('company_description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company_description_bn" class="form-label">{{ __('client.company_description_bengali') }}</label>
                            <textarea class="form-control @error('company_description_bn') is-invalid @enderror" 
                                      id="company_description_bn" name="company_description_bn" rows="3"
                                      placeholder="{{ __('client.company_description_bn_placeholder') }}">{{ old('company_description_bn', $config->company_description_bn ?? '') }}</textarea>
                            @error('company_description_bn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.bengali_description_optional') }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="welcome_message_en" class="form-label">{{ __('client.welcome_message_english') }}</label>
                            <textarea class="form-control @error('welcome_message_en') is-invalid @enderror" 
                                      id="welcome_message_en" name="welcome_message_en" rows="3"
                                      placeholder="{{ __('client.welcome_message_placeholder') }}">{{ old('welcome_message_en', $config->welcome_message_en ?? '') }}</textarea>
                            @error('welcome_message_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.welcome_message_help') }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="welcome_message_bn" class="form-label">{{ __('client.welcome_message_bengali') }}</label>
                            <textarea class="form-control @error('welcome_message_bn') is-invalid @enderror" 
                                      id="welcome_message_bn" name="welcome_message_bn" rows="3"
                                      placeholder="{{ __('client.welcome_message_bn_placeholder') }}">{{ old('welcome_message_bn', $config->welcome_message_bn ?? '') }}</textarea>
                            @error('welcome_message_bn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.bengali_welcome_message_optional') }}</small>
                            </div>
                        </div>

                        <div class="row" id="budget_options_section">
                            <div class="col-12">
                                <h5 class="mb-3">{{ __('client.budget_options') }}</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control" name="budget_options[]" 
                                               value="{{ $config->budget_options[0] ?? '৫০০-১০০০' }}" 
                                               placeholder="{{ __('client.budget_range_1') }}">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control" name="budget_options[]" 
                                               value="{{ $config->budget_options[1] ?? '১০০০-৫০০০' }}" 
                                               placeholder="{{ __('client.budget_range_2') }}">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control" name="budget_options[]" 
                                               value="{{ $config->budget_options[2] ?? '৫০০০+' }}" 
                                               placeholder="{{ __('client.budget_range_3') }}">
                                    </div>
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">{{ __('client.budget_options_help') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5 class="mb-3">{{ __('client.collection_settings') }}</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="collect_name" name="collect_name" 
                                               {{ old('collect_name', $config->collect_name ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_name">{{ __('client.collect_customer_name') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="collect_phone" name="collect_phone" 
                                               {{ old('collect_phone', $config->collect_phone ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_phone">{{ __('client.collect_phone_number') }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="collect_address" name="collect_address" 
                                               {{ old('collect_address', $config->collect_address ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_address">{{ __('client.collect_address') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="collect_budget" name="collect_budget" 
                                               {{ old('collect_budget', $config->collect_budget ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_budget">{{ __('client.collect_budget') }}</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="collect_requirements" name="collect_requirements" 
                                               {{ old('collect_requirements', $config->collect_requirements ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collect_requirements">{{ __('client.collect_requirements') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="auto_assign_numbers" name="auto_assign_numbers" 
                                               {{ old('auto_assign_numbers', $config->auto_assign_numbers ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_assign_numbers">{{ __('client.auto_assign_numbers') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('client.chat-bot.faqs.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>{{ __('common.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>{{ __('client.save_business_settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.business_type_guide') }}</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="businessTypeGuide">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#software-business">
                                    {{ __('client.software_company') }}
                                </button>
                            </h2>
                            <div id="software-business" class="accordion-collapse collapse" data-bs-parent="#businessTypeGuide">
                                <div class="accordion-body">
                                    {{ __('client.software_business_desc') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#restaurant-business">
                                    {{ __('client.restaurant') }}
                                </button>
                            </h2>
                            <div id="restaurant-business" class="accordion-collapse collapse" data-bs-parent="#businessTypeGuide">
                                <div class="accordion-body">
                                    {{ __('client.restaurant_business_desc') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#salon-business">
                                    {{ __('client.salon_spa') }}
                                </button>
                            </h2>
                            <div id="salon-business" class="accordion-collapse collapse" data-bs-parent="#businessTypeGuide">
                                <div class="accordion-body">
                                    {{ __('client.salon_business_desc') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.current_config') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('client.business_type') }}:</span>
                        <span class="badge bg-primary">{{ ucfirst($config->business_type ?? 'Not Set') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('client.inquiry_type') }}:</span>
                        <span class="badge bg-info">{{ ucfirst($config->inquiry_type ?? 'Order') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('client.default_language') }}:</span>
                        <span class="badge bg-success">{{ strtoupper($config->default_language ?? 'EN') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>{{ __('client.currency') }}:</span>
                        <span class="badge bg-warning">{{ $config->currency ?? 'BDT' }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.important_notes') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="ri-information-line text-info me-2"></i>
                            {{ __('client.settings_note_1') }}
                        </li>
                        <li class="mb-2">
                            <i class="ri-information-line text-info me-2"></i>
                            {{ __('client.settings_note_2') }}
                        </li>
                        <li class="mb-2">
                            <i class="ri-information-line text-info me-2"></i>
                            {{ __('client.settings_note_3') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const businessType = document.getElementById('business_type');
    const inquiryType = document.getElementById('inquiry_type');
    const budgetSection = document.getElementById('budget_options_section');
    
    function updateInquiryOptions() {
        const type = businessType.value;
        const currentInquiry = inquiryType.value;
        
        // Clear and set options based on business type
        inquiryType.innerHTML = '';
        
        if (type === 'software') {
            inquiryType.innerHTML = `
                <option value="consultation">{{ __('client.consultations') }}</option>
                <option value="inquiry">{{ __('client.project_inquiries') }}</option>
                <option value="order">{{ __('client.service_orders') }}</option>
            `;
        } else if (type === 'restaurant') {
            inquiryType.innerHTML = `
                <option value="order">{{ __('client.food_orders') }}</option>
                <option value="booking">{{ __('client.table_reservations') }}</option>
                <option value="inquiry">{{ __('client.general_inquiries') }}</option>
            `;
        } else if (type === 'salon') {
            inquiryType.innerHTML = `
                <option value="appointment">{{ __('client.appointments') }}</option>
                <option value="booking">{{ __('client.service_bookings') }}</option>
                <option value="inquiry">{{ __('client.service_inquiries') }}</option>
            `;
        } else {
            inquiryType.innerHTML = `
                <option value="order">{{ __('client.orders') }}</option>
                <option value="booking">{{ __('client.service_bookings') }}</option>
                <option value="appointment">{{ __('client.appointments') }}</option>
                <option value="consultation">{{ __('client.consultations') }}</option>
                <option value="inquiry">{{ __('client.general_inquiries') }}</option>
            `;
        }
        
        // Try to maintain current selection
        if (currentInquiry && inquiryType.querySelector(`option[value="${currentInquiry}"]`)) {
            inquiryType.value = currentInquiry;
        }
    }
    
    businessType.addEventListener('change', updateInquiryOptions);
    
    // Initialize on page load
    if (businessType.value) {
        updateInquiryOptions();
    }
});
</script>
@endpush