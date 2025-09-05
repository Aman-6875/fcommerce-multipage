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
                    <!-- Session Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ __('common.validation_errors') }}:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

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
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $order->order_number }}</span>
                                                <br><small class="text-muted">ID: {{ $order->id }}</small>
                                            </td>
                                            <td>
                                                @if($order->customer_info)
                                                    @php
                                                        $customerInfo = is_array($order->customer_info) ? $order->customer_info : json_decode($order->customer_info, true);
                                                    @endphp
                                                    <strong>{{ $customerInfo['name'] ?? 'N/A' }}</strong><br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone"></i> {{ $customerInfo['phone'] ?? 'N/A' }}<br>
                                                        @if(isset($customerInfo['email']) && $customerInfo['email'])
                                                            <i class="fas fa-envelope"></i> {{ $customerInfo['email'] }}
                                                        @endif
                                                    </small>
                                                @else
                                                    <span class="text-muted">{{ __('common.no_data') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->orderMeta && $order->orderMeta->count() > 0)
                                                    @foreach($order->orderMeta as $meta)
                                                        <div class="mb-1">
                                                            <strong>{{ $meta->product_name }}</strong><br>
                                                            <small class="text-muted">
                                                                {{ __('common.quantity') }}: {{ $meta->quantity }} × 
                                                                ৳{{ number_format($meta->unit_price, 2) }}
                                                                @if($meta->discount_amount > 0)
                                                                    - ৳{{ number_format($meta->discount_amount, 2) }} =
                                                                @else
                                                                    =
                                                                @endif
                                                                ৳{{ number_format($meta->total_price, 2) }}
                                                            </small>
                                                        </div>
                                                        @if(!$loop->last)<hr class="my-1">@endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">{{ __('common.no_products') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>৳{{ number_format($order->total_amount ?? 0, 2) }}</strong>
                                                @if($order->subtotal && $order->subtotal != $order->total_amount)
                                                    <br><small class="text-muted">
                                                        Subtotal: ৳{{ number_format($order->subtotal, 2) }}
                                                        @if($order->discount_amount > 0)
                                                            <br>Discount: -৳{{ number_format($order->discount_amount, 2) }}
                                                        @endif
                                                        @if($order->shipping_charge > 0)
                                                            <br>Shipping: +৳{{ number_format($order->shipping_charge, 2) }}
                                                        @endif
                                                        @if($order->advance_payment > 0)
                                                            <br>Advance: ৳{{ number_format($order->advance_payment, 2) }}
                                                        @endif
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($order->payment_method)
                                                    @case('cod')
                                                        <span class="badge bg-warning">{{ __('client.cod') }}</span>
                                                        @break
                                                    @case('online')
                                                        <span class="badge bg-info">{{ __('client.online_payment') }}</span>
                                                        @break
                                                    @case('bank_transfer')
                                                        <span class="badge bg-secondary">{{ __('client.bank_transfer') }}</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark">{{ $order->payment_method ?? 'N/A' }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @switch($order->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">{{ __('common.pending') }}</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge bg-info">{{ __('common.confirmed') }}</span>
                                                        @break
                                                    @case('processing')
                                                        <span class="badge bg-primary">{{ __('common.processing') }}</span>
                                                        @break
                                                    @case('shipped')
                                                        <span class="badge bg-secondary">{{ __('common.shipped') }}</span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge bg-success">{{ __('common.delivered') }}</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">{{ __('common.cancelled') }}</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark">{{ $order->status ?? 'N/A' }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <small>{{ $order->created_at ? $order->created_at->format('d M, Y') : 'N/A' }}</small><br>
                                                <small class="text-muted">{{ $order->created_at ? $order->created_at->format('h:i A') : '' }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('client.orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="{{ __('common.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(in_array($order->status, ['pending', 'confirmed']))
                                                        <button class="btn btn-sm btn-outline-secondary" onclick="editOrder({{ $order->id }})" title="{{ __('common.edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-cogs"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @if($order->status === 'pending')
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'confirmed'); return false;">
                                                                    <i class="fas fa-check text-success"></i> {{ __('common.confirm') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'confirmed')
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'processing'); return false;">
                                                                    <i class="fas fa-cog text-info"></i> {{ __('common.processing') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'processing')
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'shipped'); return false;">
                                                                    <i class="fas fa-truck text-primary"></i> {{ __('common.shipped') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'shipped')
                                                                <li><a class="dropdown-item" href="#" onclick="updateStatus({{ $order->id }}, 'delivered'); return false;">
                                                                    <i class="fas fa-check-circle text-success"></i> {{ __('common.delivered') }}
                                                                </a></li>
                                                            @endif
                                                            @if(!in_array($order->status, ['delivered', 'cancelled']))
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus({{ $order->id }}, 'cancelled'); return false;">
                                                                    <i class="fas fa-times"></i> {{ __('common.cancel') }}
                                                                </a></li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="{{ route('client.orders.invoice', $order) }}" target="_blank">
                                                                <i class="fas fa-print"></i> {{ __('common.print_invoice') }}
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox"></i> {{ __('client.no_orders_found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Orders -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-receipt text-muted" style="font-size: 4rem;"></i>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>{{ __('client.create_new_order') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="createOrderForm" action="{{ route('client.orders.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.customer_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="facebook_page_id">{{ __('client.facebook_page') }}</label>
                                <select class="form-control" id="facebook_page_id" name="facebook_page_id">
                                    <option value="">{{ __('common.all_pages') }}</option>
                                    @foreach(auth('client')->user()->facebookPages()->where('is_connected', true)->get() as $page)
                                        <option value="{{ $page->page_id }}" data-page-name="{{ $page->page_name }}">{{ $page->page_name }}</option>
                                    @endforeach
                                </select>
                                <div id="selected_page_info" class="mt-2" style="display: none;">
                                    <div class="alert alert-info py-2">
                                        <i class="fab fa-facebook text-primary"></i> 
                                        <strong>{{ __('client.active_page') }}:</strong> 
                                        <span id="selected_page_name"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_search_input">{{ __('common.search_customer') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="customer_search_input" 
                                           placeholder="{{ __('common.type_name_or_phone') }}" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary" id="create_new_customer_btn">
                                        <i class="fas fa-plus"></i> {{ __('common.create_new') }}
                                    </button>
                                </div>
                                <div id="customer_search_results" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                                <input type="hidden" id="customer_id" name="customer_id">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_name">{{ __('common.customer_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_info[name]" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_phone">{{ __('common.phone') }} <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_info[phone]" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_email">{{ __('common.email') }}</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_info[email]">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_address">{{ __('common.address') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="customer_address" name="customer_info[address]" rows="3" required></textarea>
                            </div>
                        </div>
                        
                        <!-- Order Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.order_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="payment_method">{{ __('client.payment_method') }}</label>
                                <select class="form-control" id="payment_method" name="payment_method">
                                    <option value="cod" selected>{{ __('client.cash_on_delivery') }}</option>
                                    <option value="online">{{ __('client.online_payment') }}</option>
                                    <option value="bank_transfer">{{ __('client.bank_transfer') }}</option>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="shipping_charge">{{ __('client.shipping_charge') }} (৳)</label>
                                        <input type="number" class="form-control" id="shipping_charge" name="shipping_charge" min="0" step="0.01" value="0">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="advance_payment">{{ __('client.advance_payment') }} (৳)</label>
                                        <input type="number" class="form-control" id="advance_payment" name="advance_payment" min="0" step="0.01" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="discount_type">{{ __('client.discount_type') }}</label>
                                        <select class="form-control" id="discount_type" name="discount_type">
                                            <option value="fixed">{{ __('client.fixed_amount') }}</option>
                                            <option value="percentage">{{ __('client.percentage') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="order_discount">{{ __('client.order_discount') }}</label>
                                        <input type="number" class="form-control" id="order_discount" name="discount_amount" min="0" step="0.01" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="notes">{{ __('common.notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="{{ __('client.order_notes_placeholder') }}"></textarea>
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
                                        <!-- Product rows will be added here -->
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
                                                    <td class="text-end"><strong id="orderSubtotal">৳0.00</strong></td>
                                                </tr>
                                                <tr id="orderDiscountRow" style="display: none;">
                                                    <td><strong>{{ __('client.order_discount') }}:</strong></td>
                                                    <td class="text-end"><strong id="orderDiscountAmount">-৳0.00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('client.shipping') }}:</strong></td>
                                                    <td class="text-end"><strong id="shippingAmount">৳0.00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>{{ __('client.total_amount') }}:</strong></td>
                                                    <td class="text-end"><strong id="totalAmount" class="text-primary">৳0.00</strong></td>
                                                </tr>
                                                <tr id="advancePaymentRow" style="display: none;">
                                                    <td><strong>{{ __('client.advance_payment') }}:</strong></td>
                                                    <td class="text-end"><strong id="advanceAmount">৳0.00</strong></td>
                                                </tr>
                                                <tr id="remainingRow" style="display: none;">
                                                    <td><strong>{{ __('client.remaining_amount') }}:</strong></td>
                                                    <td class="text-end"><strong id="remainingAmount" class="text-warning">৳0.00</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>{{ __('client.edit_order') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_order_id" name="order_id">
                    <!-- Same content as create modal but with different IDs for editing -->
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>{{ __('common.loading') }}...</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="updateOrder()">
                    <i class="fas fa-save"></i> {{ __('client.update_order') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Order management JavaScript
let productRowCounter = 0;

$(document).ready(function() {
    // Add first product row when modal opens
    $('#createOrderModal').on('shown.bs.modal', function() {
        if ($('#productRows').children().length === 0) {
            addProductRow();
        }
    });
    
    // Customer search functionality
    let searchTimeout;
    $('#customer_search_input').on('input', function() {
        clearTimeout(searchTimeout);
        const term = $(this).val().trim();
        
        if (term.length < 2) {
            $('#customer_search_results').hide();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchCustomers(term);
        }, 300);
    });
    
    // Handle Facebook page change
    $('#facebook_page_id').change(function() {
        const selectedOption = $(this).find(':selected');
        const pageName = selectedOption.data('page-name');
        
        if (pageName) {
            $('#selected_page_name').text(pageName);
            $('#selected_page_info').show();
        } else {
            $('#selected_page_info').hide();
        }
        
        $('#customer_search_input').val('');
        $('#customer_id').val('');
        clearCustomerForm();
        $('#customer_search_results').hide();
        
        // Update placeholder text based on page selection
        if (pageName) {
            $('#customer_search_input').attr('placeholder', `{{ __('common.search_customers_from') }} ${pageName}`);
        } else {
            $('#customer_search_input').attr('placeholder', '{{ __('common.type_name_or_phone') }}');
        }
    });
    
    // Create new customer button
    $('#create_new_customer_btn').click(function() {
        $('#customer_id').val('');
        $('#customer_search_input').val('');
        clearCustomerForm();
        $('#customer_search_results').hide();
        $('#customer_name').focus();
    });
    
    // Hide search results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#customer_search_input, #customer_search_results').length) {
            $('#customer_search_results').hide();
        }
    });
    
    // Calculate totals when values change
    $(document).on('input', '#shipping_charge, #advance_payment, #order_discount', calculateOrderTotal);
    $('#discount_type').change(calculateOrderTotal);
});

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
    
    // Bind events to new row
    bindProductRowEvents(productRowCounter);
}

function bindProductRowEvents(rowId) {
    // Product selection
    $(document).on('change', `[data-row="${rowId}"].product-select`, function() {
        const selectedOption = $(this).find(':selected');
        const price = selectedOption.data('price') || 0;
        const name = selectedOption.data('name') || '';
        
        $(`[data-row="${rowId}"].product-price`).val(price);
        $(`[data-row="${rowId}"] .product-name`).val(name);
        calculateProductTotal(rowId);
    });
    
    // Quantity, price, discount changes
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
    
    // Calculate products subtotal
    $('.product-total').each(function() {
        const amount = parseFloat($(this).text().replace('৳', '')) || 0;
        subtotal += amount;
    });
    
    // Get other values
    const shipping = parseFloat($('#shipping_charge').val()) || 0;
    const advance = parseFloat($('#advance_payment').val()) || 0;
    const discountAmount = parseFloat($('#order_discount').val()) || 0;
    const discountType = $('#discount_type').val();
    
    // Calculate order discount
    let orderDiscount = 0;
    if (discountType === 'percentage') {
        orderDiscount = (subtotal * discountAmount) / 100;
    } else {
        orderDiscount = Math.min(discountAmount, subtotal);
    }
    
    const finalSubtotal = subtotal - orderDiscount;
    const total = finalSubtotal + shipping;
    const remaining = total - advance;
    
    // Update display
    $('#orderSubtotal').text('৳' + subtotal.toFixed(2));
    $('#shippingAmount').text('৳' + shipping.toFixed(2));
    $('#totalAmount').text('৳' + total.toFixed(2));
    
    // Show/hide discount row
    if (orderDiscount > 0) {
        $('#orderDiscountRow').show();
        $('#orderDiscountAmount').text('-৳' + orderDiscount.toFixed(2));
    } else {
        $('#orderDiscountRow').hide();
    }
    
    // Show/hide advance payment rows
    if (advance > 0) {
        $('#advancePaymentRow, #remainingRow').show();
        $('#advanceAmount').text('৳' + advance.toFixed(2));
        $('#remainingAmount').text('৳' + remaining.toFixed(2));
    } else {
        $('#advancePaymentRow, #remainingRow').hide();
    }
}

function createOrder() {
    const formData = new FormData(document.getElementById('createOrderForm'));
    
    // Basic validation
    if (!formData.get('customer_info[name]') || !formData.get('customer_info[phone]')) {
        alert('{{ __("common.please_fill_required_fields") }}');
        return;
    }
    
    // Check if at least one product is added
    if ($('#productRows tr').length === 0) {
        alert('{{ __("client.please_add_at_least_one_product") }}');
        return;
    }
    
    $.ajax({
        url: '{{ route("client.orders.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#createOrderModal').modal('hide');
            if (response.success) {
                alert(response.message || 'Order created successfully!');
                if (response.redirect_url) {
                    window.location.href = response.redirect_url;
                } else {
                    location.reload();
                }
            } else {
                alert('Error: ' + (response.message || 'Unknown error occurred'));
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = '{{ __("common.validation_errors") }}:\n';
                Object.keys(errors).forEach(key => {
                    errorMessage += `- ${errors[key][0]}\n`;
                });
                alert(errorMessage);
            } else {
                alert('{{ __("common.error_occurred") }}: ' + xhr.responseText);
            }
        }
    });
}

function updateStatus(orderId, status) {
    console.log('updateStatus called with:', orderId, status);
    
    if(confirm(`{{ __('client.confirm_status_change') }} ${status}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/client/orders/' + orderId + '/status';
        
        console.log('Form action:', form.action);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('CSRF Token:', csrfToken ? 'Found' : 'Missing');
        
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${status}">
        `;
        
        console.log('Form HTML:', form.innerHTML);
        
        document.body.appendChild(form);
        console.log('About to submit form...');
        form.submit();
    } else {
        console.log('User cancelled status update');
    }
}

function resetFilters() {
    $('#orderSearch').val('');
    $('#statusFilter').val('');
    $('#paymentFilter').val('');
    $('#dateFilter').val('');
    window.location.reload();
}

function exportOrders() {
    const params = new URLSearchParams({
        status: $('#statusFilter').val() || '',
        search: $('#orderSearch').val() || '',
        date_from: $('#dateFilter').val() || '',
        payment_method: $('#paymentFilter').val() || ''
    });
    
    window.open('/client/orders/export/excel?' + params.toString(), '_blank');
}

function searchCustomers(term) {
    const pageId = $('#facebook_page_id').val();
    
    $.ajax({
        url: '{{ route("client.orders.search-customers") }}',
        method: 'GET',
        data: {
            term: term,
            page_id: pageId
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                showCustomerResults(response.data);
            } else {
                showNoCustomerFound(term);
            }
        },
        error: function(xhr) {
            console.error('Customer search error:', xhr.responseText);
            $('#customer_search_results').hide();
        }
    });
}

function showCustomerResults(customers) {
    let html = '';
    const selectedPageId = $('#facebook_page_id').val();
    const selectedPageName = $('#facebook_page_id option:selected').data('page-name');
    
    customers.forEach(customer => {
        html += `
            <div class="dropdown-item customer-result" data-customer='${JSON.stringify(customer)}' style="cursor: pointer; border-bottom: 1px solid #eee;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${customer.name}</strong>
                        <br><small class="text-muted">${customer.phone}</small>
                        ${customer.email ? `<br><small class="text-muted">${customer.email}</small>` : ''}
                    </div>
                    <div class="text-end">
                        ${customer.page_name ? `<span class="badge bg-primary">${customer.page_name}</span>` : '<span class="badge bg-secondary">No Page</span>'}
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#customer_search_results').html(html).show();
    
    // Handle customer selection
    $('.customer-result').click(function() {
        const customer = JSON.parse($(this).attr('data-customer'));
        selectCustomer(customer);
    });
}

function showNoCustomerFound(term) {
    const html = `
        <div class="dropdown-item" style="cursor: pointer;" onclick="createNewCustomerFromSearch('${term}')">
            <div class="text-center text-muted">
                <i class="fas fa-user-plus"></i> Create new customer: "${term}"
            </div>
        </div>
    `;
    $('#customer_search_results').html(html).show();
}

function selectCustomer(customer) {
    $('#customer_id').val(customer.id);
    $('#customer_search_input').val(customer.display_name);
    $('#customer_name').val(customer.name);
    $('#customer_phone').val(customer.phone);
    $('#customer_email').val(customer.email || '');
    $('#customer_address').val(customer.address || '');
    $('#customer_search_results').hide();
}

function createNewCustomerFromSearch(term) {
    $('#customer_id').val('');
    $('#customer_search_input').val('');
    $('#customer_search_results').hide();
    
    // Try to detect if term is phone number or name
    const isPhone = /^[\d\s\-\+\(\)]+$/.test(term.trim());
    if (isPhone) {
        $('#customer_phone').val(term.trim());
        $('#customer_name').focus();
    } else {
        $('#customer_name').val(term.trim());
        $('#customer_phone').focus();
    }
}

function clearCustomerForm() {
    $('#customer_name').val('');
    $('#customer_phone').val('');
    $('#customer_email').val('');
    $('#customer_address').val('');
}

function editOrder(orderId) {
    // Load order data and show edit modal
    $.ajax({
        url: `/client/orders/${orderId}/edit`,
        method: 'GET',
        success: function(response) {
            // For now, redirect to edit page until we implement the edit modal
            window.location.href = `/client/orders/${orderId}/edit`;
        },
        error: function(xhr) {
            alert('{{ __("common.error_occurred") }}: ' + xhr.responseText);
        }
    });
}
</script>
@endpush