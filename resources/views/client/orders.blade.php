@extends('layouts.client')

@section('title', __('client.orders'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('client.orders') }}</h4>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-warning">{{ auth('client')->user()->orders()->where('status', 'pending')->count() ?? 0 }}</span> 
                            {{ __('client.pending_orders') }}
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                            <i class="fas fa-plus"></i> {{ __('client.create_order') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Stats -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->orders()->count() ?? 0 }}</h4>
                                    <small>{{ __('client.total_orders') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->orders()->where('status', 'pending')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.pending') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->orders()->where('status', 'confirmed')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.confirmed') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->orders()->where('status', 'delivered')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.delivered') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->orders()->where('status', 'cancelled')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.cancelled') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h4>৳{{ number_format(auth('client')->user()->orders()->where('status', 'delivered')->sum('total_amount') ?? 0) }}</h4>
                                    <small>{{ __('client.total_revenue') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($orders->count() > 0)
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="{{ __('common.search') }} {{ __('client.orders') }}..." id="orderSearch">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">{{ __('common.all_status') }}</option>
                                    <option value="pending">{{ __('common.pending') }}</option>
                                    <option value="confirmed">{{ __('common.confirmed') }}</option>
                                    <option value="processing">{{ __('common.processing') }}</option>
                                    <option value="shipped">{{ __('common.shipped') }}</option>
                                    <option value="delivered">{{ __('common.delivered') }}</option>
                                    <option value="cancelled">{{ __('common.cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="paymentFilter">
                                    <option value="">{{ __('common.all_payments') }}</option>
                                    <option value="cod">{{ __('client.cod') }}</option>
                                    <option value="online">{{ __('client.online_payment') }}</option>
                                    <option value="bank_transfer">{{ __('client.bank_transfer') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" id="dateFilter">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="fas fa-sync"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="exportOrders()">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Orders Table -->
                        <div class="table-responsive">
                            <table class="table table-striped" id="ordersTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('client.order_number') }}</th>
                                        <th>{{ __('common.customer') }}</th>
                                        <th>{{ __('client.product') }}</th>
                                        <th>{{ __('common.amount') }}</th>
                                        <th>{{ __('common.payment') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.date') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Sample data - will be populated by AJAX -->
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin"></i> {{ __('common.loading') }}...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Orders -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-shopping-cart text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.no_orders_yet') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.orders_will_appear_here') }}</p>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                                    <i class="fas fa-plus"></i> {{ __('client.create_first_order') }}
                                </button>
                                
                                @if(auth('client')->user()->facebookPages()->where('is_connected', true)->count() === 0)
                                    <a href="{{ route('client.facebook-pages') }}" class="btn btn-outline-primary">
                                        <i class="fab fa-facebook"></i> {{ __('client.connect_facebook_first') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.create_new_order') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm">
                    @csrf
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.customer_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="customer_name">{{ __('common.customer_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_phone">{{ __('common.phone') }} <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_email">{{ __('common.email') }}</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_address">{{ __('common.address') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="customer_address" name="customer_address" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Product Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.product_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="product_name">{{ __('client.product_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="quantity">{{ __('common.quantity') }} <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="unit_price">{{ __('client.unit_price') }} (৳) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="unit_price" name="unit_price" min="0" step="0.01" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="discount">{{ __('client.discount') }} (৳)</label>
                                <input type="number" class="form-control" id="discount" name="discount" min="0" step="0.01" value="0">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="payment_method">{{ __('client.payment_method') }}</label>
                                <select class="form-control" id="payment_method" name="payment_method">
                                    <option value="cod" selected>{{ __('client.cash_on_delivery') }}</option>
                                    <option value="online">{{ __('client.online_payment') }}</option>
                                    <option value="bank_transfer">{{ __('client.bank_transfer') }}</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>{{ __('client.total_amount') }}:</strong> 
                                <span id="totalAmount">৳0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes">{{ __('common.notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="{{ __('client.order_notes_placeholder') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="createOrder()">
                    <i class="fas fa-save"></i> {{ __('client.create_order') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate total amount when quantity, unit price, or discount changes
    $('#quantity, #unit_price, #discount').on('input', function() {
        calculateTotal();
    });
    
    // Load orders data
    loadOrders();
});

function calculateTotal() {
    const quantity = parseFloat($('#quantity').val()) || 0;
    const unitPrice = parseFloat($('#unit_price').val()) || 0;
    const discount = parseFloat($('#discount').val()) || 0;
    
    const subtotal = quantity * unitPrice;
    const total = Math.max(0, subtotal - discount);
    
    $('#totalAmount').text('৳' + total.toFixed(2));
}

function loadOrders() {
    // This will be implemented when we have the orders API endpoint
    console.log('Loading orders...');
}

function createOrder() {
    // This will be implemented when we have the create order endpoint
    console.log('Creating order...');
}

function resetFilters() {
    $('#orderSearch').val('');
    $('#statusFilter').val('');
    $('#paymentFilter').val('');
    $('#dateFilter').val('');
    loadOrders();
}

function exportOrders() {
    // This will be implemented for order export functionality
    console.log('Exporting orders...');
}
</script>
@endpush