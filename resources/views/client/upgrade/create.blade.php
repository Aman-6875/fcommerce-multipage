@extends('layouts.client')

@section('title', __('client.upgrade_plan'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.upgrade_plan') }}</h4>
                    <p class="text-muted mb-0">{{ __('client.select_plan_and_payment_method') }}</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('client.upgrade.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Plan Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>{{ __('client.select_plan') }}</h5>
                            </div>
                            @foreach($plans as $key => $plan)
                                @if($key !== 'free')
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100 plan-card" data-plan="{{ $key }}">
                                            <div class="card-body text-center">
                                                <h5 class="card-title">{{ $plan['name'] }}</h5>
                                                <div class="pricing mb-3">
                                                    <div class="monthly-price">
                                                        <span class="h4">৳{{ number_format($plan['monthly']) }}</span>
                                                        <small class="text-muted">/{{ __('client.month') }}</small>
                                                    </div>
                                                    <div class="yearly-price" style="display: none;">
                                                        <span class="h4">৳{{ number_format($plan['yearly']) }}</span>
                                                        <small class="text-muted">/{{ __('client.year') }}</small>
                                                        <div class="badge badge-success">{{ __('client.save_16_percent') }}</div>
                                                    </div>
                                                </div>
                                                <input type="radio" name="requested_plan" value="{{ $key }}" class="plan-radio" style="transform: scale(1.5);">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Billing Cycle -->
                        <div class="form-group mb-4">
                            <label>{{ __('client.billing_cycle') }}</label>
                            <div class="btn-group d-block" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="billing_cycle" value="monthly" checked> {{ __('client.monthly') }}
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="billing_cycle" value="yearly"> {{ __('client.yearly') }}
                                </label>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-group mb-3">
                            <label for="payment_method">{{ __('client.payment_method') }}</label>
                            <select class="form-control" name="payment_method" id="payment_method" required>
                                <option value="">{{ __('common.select') }}</option>
                                @foreach($paymentMethods as $key => $method)
                                    <option value="{{ $key }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Details -->
                        <div class="payment-details" style="display: none;">
                            <div class="alert alert-info">
                                <h6>{{ __('client.payment_instructions') }}</h6>
                                <div id="payment-info"></div>
                            </div>
                        </div>

                        <!-- Transaction ID -->
                        <div class="form-group mb-3">
                            <label for="transaction_id">{{ __('client.transaction_id') }} <small class="text-muted">({{ __('common.optional') }})</small></label>
                            <input type="text" class="form-control" name="transaction_id" id="transaction_id" placeholder="{{ __('client.transaction_id_placeholder') }}">
                        </div>

                        <!-- Payment Proof -->
                        <div class="form-group mb-3">
                            <label for="payment_proof">{{ __('client.payment_proof') }} <small class="text-muted">({{ __('common.optional') }})</small></label>
                            <input type="file" class="form-control-file" name="payment_proof" id="payment_proof" accept="image/*">
                            <small class="form-text text-muted">{{ __('client.upload_payment_screenshot') }}</small>
                        </div>

                        <!-- Notes -->
                        <div class="form-group mb-4">
                            <label for="notes">{{ __('client.additional_notes') }} <small class="text-muted">({{ __('common.optional') }})</small></label>
                            <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="{{ __('client.notes_placeholder') }}"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning btn-lg w-100">
                                <i class="fas fa-paper-plane"></i> {{ __('client.submit_upgrade_request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment Information -->
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('client.payment_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="payment-method-info">
                        <p class="text-muted">{{ __('client.select_payment_method_to_see_details') }}</p>
                    </div>
                </div>
            </div>

            <!-- What happens next -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6>{{ __('client.what_happens_next') }}</h6>
                </div>
                <div class="card-body">
                    <ol class="text-sm">
                        <li>{{ __('client.submit_upgrade_request_step') }}</li>
                        <li>{{ __('client.admin_review_step') }}</li>
                        <li>{{ __('client.account_upgrade_step') }}</li>
                        <li>{{ __('client.email_confirmation_step') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const billingCycleRadios = document.querySelectorAll('input[name="billing_cycle"]');
    const planCards = document.querySelectorAll('.plan-card');
    
    // Handle billing cycle change
    billingCycleRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const cycle = this.value;
            planCards.forEach(card => {
                const monthlyPrice = card.querySelector('.monthly-price');
                const yearlyPrice = card.querySelector('.yearly-price');
                
                if (cycle === 'monthly') {
                    monthlyPrice.style.display = 'block';
                    yearlyPrice.style.display = 'none';
                } else {
                    monthlyPrice.style.display = 'none';
                    yearlyPrice.style.display = 'block';
                }
            });
        });
    });

    // Handle payment method change
    const paymentMethodSelect = document.getElementById('payment_method');
    const paymentDetails = document.querySelector('.payment-details');
    const paymentInfo = document.getElementById('payment-info');

    const paymentInstructions = {
        'bkash': {
            title: 'bKash Payment',
            details: '<strong>Personal:</strong> 01XXXXXXXXX<br><strong>Agent:</strong> 01XXXXXXXXX<br>{{ __("client.send_money_and_note_transaction_id") }}'
        },
        'nagad': {
            title: 'Nagad Payment', 
            details: '<strong>Personal:</strong> 01XXXXXXXXX<br>{{ __("client.send_money_and_note_transaction_id") }}'
        },
        'rocket': {
            title: 'Rocket Payment',
            details: '<strong>Personal:</strong> 01XXXXXXXXX<br>{{ __("client.send_money_and_note_transaction_id") }}'
        },
        'bank_transfer': {
            title: 'Bank Transfer',
            details: '<strong>Bank:</strong> Dutch-Bangla Bank<br><strong>Account:</strong> 1234567890<br><strong>Name:</strong> Your Company Name<br>{{ __("client.transfer_money_and_upload_receipt") }}'
        },
        'cash': {
            title: 'Cash Payment',
            details: '{{ __("client.contact_admin_for_cash_payment") }}'
        }
    };

    paymentMethodSelect.addEventListener('change', function() {
        const method = this.value;
        if (method && paymentInstructions[method]) {
            paymentInfo.innerHTML = paymentInstructions[method].details;
            paymentDetails.style.display = 'block';
        } else {
            paymentDetails.style.display = 'none';
        }
    });

    // Plan selection styling
    document.querySelectorAll('.plan-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            planCards.forEach(card => card.classList.remove('border-warning'));
            this.closest('.plan-card').classList.add('border-warning');
        });
    });
});
</script>

<style>
.plan-card {
    cursor: pointer;
    transition: all 0.2s;
}
.plan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.plan-card.border-warning {
    border-color: #ffc107 !important;
    border-width: 2px;
}
</style>
@endpush