@extends('layouts.client')

@section('title', __('client.edit_order'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">{{ __('client.edit_order') }} - #{{ $order->order_number }}</h4>
                        <small class="text-muted">{{ __('common.created_at') }}: {{ $order->created_at->format('d M, Y h:i A') }}</small>
                    </div>
                    <a href="{{ route('client.orders.show', $order) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.orders.update', $order) }}" method="POST" id="editOrderForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">{{ __('client.customer_information') }}</h6>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_id">{{ __('common.existing_customer') }}</label>
                                    <select class="form-control" id="customer_id" name="customer_id">
                                        <option value="">{{ __('common.select_customer_or_create_new') }}</option>
                                        @foreach(auth('client')->user()->customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    {{ $order->customer_id == $customer->id ? 'selected' : '' }}
                                                    data-name="{{ $customer->name }}" 
                                                    data-phone="{{ $customer->phone }}" 
                                                    data-email="{{ $customer->email }}">
                                                {{ $customer->name }} - {{ $customer->phone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_name">{{ __('common.customer_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('customer_info.name') is-invalid @enderror" 
                                           id="customer_name" name="customer_info[name]" 
                                           value="{{ old('customer_info.name', $order->customer_info['name'] ?? '') }}" required>
                                    @error('customer_info.name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_phone">{{ __('common.phone') }} <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('customer_info.phone') is-invalid @enderror" 
                                           id="customer_phone" name="customer_info[phone]" 
                                           value="{{ old('customer_info.phone', $order->customer_info['phone'] ?? '') }}" required>
                                    @error('customer_info.phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_email">{{ __('common.email') }}</label>
                                    <input type="email" class="form-control @error('customer_info.email') is-invalid @enderror" 
                                           id="customer_email" name="customer_info[email]" 
                                           value="{{ old('customer_info.email', $order->customer_info['email'] ?? '') }}">
                                    @error('customer_info.email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="customer_address">{{ __('common.address') }} <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('customer_info.address') is-invalid @enderror" 
                                              id="customer_address" name="customer_info[address]" rows="3" required>{{ old('customer_info.address', $order->customer_info['address'] ?? '') }}</textarea>
                                    @error('customer_info.address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Order Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">{{ __('client.order_information') }}</h6>
                                
                                <div class="form-group mb-3">
                                    <label for="payment_method">{{ __('client.payment_method') }}</label>
                                    <select class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                                        <option value="cod" {{ old('payment_method', $order->payment_method) == 'cod' ? 'selected' : '' }}>{{ __('client.cash_on_delivery') }}</option>
                                        <option value="online" {{ old('payment_method', $order->payment_method) == 'online' ? 'selected' : '' }}>{{ __('client.online_payment') }}</option>
                                        <option value="bank_transfer" {{ old('payment_method', $order->payment_method) == 'bank_transfer' ? 'selected' : '' }}>{{ __('client.bank_transfer') }}</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="shipping_charge">{{ __('client.shipping_charge') }} (৳)</label>
                                            <input type="number" class="form-control @error('shipping_charge') is-invalid @enderror" 
                                                   id="shipping_charge" name="shipping_charge" min="0" step="0.01" 
                                                   value="{{ old('shipping_charge', $order->shipping_charge ?? 0) }}">
                                            @error('shipping_charge')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="advance_payment">{{ __('client.advance_payment') }} (৳)</label>
                                            <input type="number" class="form-control @error('advance_payment') is-invalid @enderror" 
                                                   id="advance_payment" name="advance_payment" min="0" step="0.01" 
                                                   value="{{ old('advance_payment', $order->advance_payment ?? 0) }}">
                                            @error('advance_payment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="discount_type">{{ __('client.discount_type') }}</label>
                                            <select class="form-control @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                                <option value="fixed" {{ old('discount_type', $order->discount_type) == 'fixed' ? 'selected' : '' }}>{{ __('client.fixed_amount') }}</option>
                                                <option value="percentage" {{ old('discount_type', $order->discount_type) == 'percentage' ? 'selected' : '' }}>{{ __('client.percentage') }}</option>
                                            </select>
                                            @error('discount_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group mb-3">
                                            <label for="order_discount">{{ __('client.order_discount') }}</label>
                                            <input type="number" class="form-control @error('discount_amount') is-invalid @enderror" 
                                                   id="order_discount" name="discount_amount" min="0" step="0.01" 
                                                   value="{{ old('discount_amount', $order->discount_amount ?? 0) }}">
                                            @error('discount_amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="notes">{{ __('common.notes') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="2" 
                                              placeholder="{{ __('client.order_notes_placeholder') }}">{{ old('notes', $order->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Products Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6>{{ __('client.products') }}</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addProductRow()">
                                        <i class="fas fa-plus"></i> {{ __('client.add_product') }}
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="productsTable">
                                        <thead>
                                            <tr>
                                                <th width="30%">{{ __('client.product') }}</th>
                                                <th width="15%">{{ __('common.quantity') }}</th>
                                                <th width="15%">{{ __('client.unit_price') }}</th>
                                                <th width="15%">{{ __('client.discount') }}</th>
                                                <th width="15%">{{ __('common.total') }}</th>
                                                <th width="10%">{{ __('common.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productRows">
                                            @foreach($order->orderMeta as $index => $meta)
                                            <tr id="productRow{{ $index }}">
                                                <td>
                                                    <select class="form-control product-select" name="products[{{ $index }}][product_id]" data-row="{{ $index }}" required>
                                                        <option value="">{{ __('common.select_product') }}</option>
                                                        @foreach(auth('client')->user()->products->where('is_active', true) as $product)
                                                            <option value="{{ $product->id }}" 
                                                                    {{ $meta->product_id == $product->id ? 'selected' : '' }}
                                                                    data-price="{{ $product->sale_price ?: $product->price }}" 
                                                                    data-name="{{ $product->name }}">
                                                                {{ $product->name }} - ৳{{ $product->sale_price ?: $product->price }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" class="product-name" name="products[{{ $index }}][product_name]" value="{{ $meta->product_name }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control product-quantity" name="products[{{ $index }}][quantity]" 
                                                           data-row="{{ $index }}" min="1" value="{{ $meta->quantity }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control product-price" name="products[{{ $index }}][unit_price]" 
                                                           data-row="{{ $index }}" min="0" step="0.01" value="{{ $meta->unit_price }}" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control product-discount" name="products[{{ $index }}][discount_amount]" 
                                                           data-row="{{ $index }}" min="0" step="0.01" value="{{ $meta->discount_amount ?? 0 }}">
                                                </td>
                                                <td>
                                                    <span class="product-total" id="productTotal{{ $index }}">৳{{ number_format($meta->total_price, 2) }}</span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductRow({{ $index }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Order Summary -->
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 offset-md-6">
                                                <table class="table table-sm">
                                                    <tr>
                                                        <td><strong>{{ __('client.subtotal') }}:</strong></td>
                                                        <td class="text-end"><strong id="orderSubtotal">৳{{ number_format($order->orderMeta->sum('total_price'), 2) }}</strong></td>
                                                    </tr>
                                                    <tr id="orderDiscountRow" style="display: {{ $order->discount_amount > 0 ? 'table-row' : 'none' }};">
                                                        <td><strong>{{ __('client.order_discount') }}:</strong></td>
                                                        <td class="text-end"><strong id="orderDiscountAmount">-৳{{ number_format($order->discount_amount, 2) }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>{{ __('client.shipping') }}:</strong></td>
                                                        <td class="text-end"><strong id="shippingAmount">৳{{ number_format($order->shipping_charge, 2) }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>{{ __('client.total_amount') }}:</strong></td>
                                                        <td class="text-end"><strong id="totalAmount" class="text-primary">৳{{ number_format($order->total_amount, 2) }}</strong></td>
                                                    </tr>
                                                    <tr id="advancePaymentRow" style="display: {{ $order->advance_payment > 0 ? 'table-row' : 'none' }};">
                                                        <td><strong>{{ __('client.advance_payment') }}:</strong></td>
                                                        <td class="text-end"><strong id="advanceAmount">৳{{ number_format($order->advance_payment, 2) }}</strong></td>
                                                    </tr>
                                                    <tr id="remainingRow" style="display: {{ $order->advance_payment > 0 ? 'table-row' : 'none' }};">
                                                        <td><strong>{{ __('client.remaining_amount') }}:</strong></td>
                                                        <td class="text-end"><strong id="remainingAmount" class="text-warning">৳{{ number_format($order->total_amount - $order->advance_payment, 2) }}</strong></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('client.orders.show', $order) }}" class="btn btn-secondary">
                                        {{ __('common.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('client.update_order') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let productRowCounter = {{ $order->orderMeta->count() }};

$(document).ready(function() {
    // Handle customer selection
    $('#customer_id').change(function() {
        const selectedOption = $(this).find(':selected');
        if (selectedOption.val()) {
            $('#customer_name').val(selectedOption.data('name'));
            $('#customer_phone').val(selectedOption.data('phone'));
            $('#customer_email').val(selectedOption.data('email'));
        }
    });
    
    // Calculate totals when values change
    $(document).on('input', '#shipping_charge, #advance_payment, #order_discount', calculateOrderTotal);
    $('#discount_type').change(calculateOrderTotal);
    
    // Bind events to existing rows
    @foreach($order->orderMeta as $index => $meta)
        bindProductRowEvents({{ $index }});
    @endforeach
});

// Same JavaScript functions as create modal (addProductRow, bindProductRowEvents, etc.)
function addProductRow() {
    productRowCounter++;
    const products = @json(auth('client')->user()->products->where('is_active', true)->values());
    
    let productOptions = '<option value="">{{ __("common.select_product") }}</option>';
    products.forEach(product => {
        const price = product.sale_price || product.price;
        productOptions += `<option value="${product.id}" data-price="${price}" data-name="${product.name}">${product.name} - ৳${price}</option>`;
    });
    
    const row = `
        <tr id="productRow${productRowCounter}">
            <td>
                <select class="form-control product-select" name="products[${productRowCounter}][product_id]" data-row="${productRowCounter}" required>
                    ${productOptions}
                </select>
                <input type="hidden" class="product-name" name="products[${productRowCounter}][product_name]">
            </td>
            <td>
                <input type="number" class="form-control product-quantity" name="products[${productRowCounter}][quantity]" 
                       data-row="${productRowCounter}" min="1" value="1" required>
            </td>
            <td>
                <input type="number" class="form-control product-price" name="products[${productRowCounter}][unit_price]" 
                       data-row="${productRowCounter}" min="0" step="0.01" required>
            </td>
            <td>
                <input type="number" class="form-control product-discount" name="products[${productRowCounter}][discount_amount]" 
                       data-row="${productRowCounter}" min="0" step="0.01" value="0">
            </td>
            <td>
                <span class="product-total" id="productTotal${productRowCounter}">৳0.00</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeProductRow(${productRowCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#productRows').append(row);
    bindProductRowEvents(productRowCounter);
}

function bindProductRowEvents(rowId) {
    $(document).on('change', `[data-row="${rowId}"].product-select`, function() {
        const selectedOption = $(this).find(':selected');
        const price = selectedOption.data('price') || 0;
        const name = selectedOption.data('name') || '';
        
        $(`[data-row="${rowId}"].product-price`).val(price);
        $(`[data-row="${rowId}"] .product-name`).val(name);
        calculateProductTotal(rowId);
    });
    
    $(document).on('input', `[data-row="${rowId}"].product-quantity, [data-row="${rowId}"].product-price, [data-row="${rowId}"].product-discount`, function() {
        calculateProductTotal(rowId);
    });
}

function removeProductRow(rowId) {
    $(`#productRow${rowId}`).remove();
    calculateOrderTotal();
}

function calculateProductTotal(rowId) {
    const quantity = parseFloat($(`[data-row="${rowId}"].product-quantity`).val()) || 0;
    const price = parseFloat($(`[data-row="${rowId}"].product-price`).val()) || 0;
    const discount = parseFloat($(`[data-row="${rowId}"].product-discount`).val()) || 0;
    
    const total = Math.max(0, (quantity * price) - discount);
    $(`#productTotal${rowId}`).text('৳' + total.toFixed(2));
    
    calculateOrderTotal();
}

function calculateOrderTotal() {
    let subtotal = 0;
    
    $('.product-total').each(function() {
        const amount = parseFloat($(this).text().replace('৳', '')) || 0;
        subtotal += amount;
    });
    
    const shipping = parseFloat($('#shipping_charge').val()) || 0;
    const advance = parseFloat($('#advance_payment').val()) || 0;
    const discountAmount = parseFloat($('#order_discount').val()) || 0;
    const discountType = $('#discount_type').val();
    
    let orderDiscount = 0;
    if (discountType === 'percentage') {
        orderDiscount = (subtotal * discountAmount) / 100;
    } else {
        orderDiscount = Math.min(discountAmount, subtotal);
    }
    
    const finalSubtotal = subtotal - orderDiscount;
    const total = finalSubtotal + shipping;
    const remaining = total - advance;
    
    $('#orderSubtotal').text('৳' + subtotal.toFixed(2));
    $('#shippingAmount').text('৳' + shipping.toFixed(2));
    $('#totalAmount').text('৳' + total.toFixed(2));
    
    if (orderDiscount > 0) {
        $('#orderDiscountRow').show();
        $('#orderDiscountAmount').text('-৳' + orderDiscount.toFixed(2));
    } else {
        $('#orderDiscountRow').hide();
    }
    
    if (advance > 0) {
        $('#advancePaymentRow, #remainingRow').show();
        $('#advanceAmount').text('৳' + advance.toFixed(2));
        $('#remainingAmount').text('৳' + remaining.toFixed(2));
    } else {
        $('#advancePaymentRow, #remainingRow').hide();
    }
}
</script>
@endpush