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
                                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatusIndex({{ $order->id }}, 'confirmed'); return false;">
                                                                    <i class="fas fa-check text-success"></i> {{ __('common.confirm') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'confirmed')
                                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatusIndex({{ $order->id }}, 'processing'); return false;">
                                                                    <i class="fas fa-cog text-info"></i> {{ __('common.processing') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'processing')
                                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatusIndex({{ $order->id }}, 'shipped'); return false;">
                                                                    <i class="fas fa-truck text-primary"></i> {{ __('common.shipped') }}
                                                                </a></li>
                                                            @endif
                                                            @if($order->status === 'shipped')
                                                                <li><a class="dropdown-item" href="#" onclick="updateOrderStatusIndex({{ $order->id }}, 'delivered'); return false;">
                                                                    <i class="fas fa-check-circle text-success"></i> {{ __('common.delivered') }}
                                                                </a></li>
                                                            @endif
                                                            @if(!in_array($order->status, ['delivered', 'cancelled']))
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item text-danger" href="#" onclick="updateOrderStatusIndex({{ $order->id }}, 'cancelled'); return false;">
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
<div class="modal fade" id="createOrderModal" tabindex="-1" role="dialog" aria-labelledby="createOrderModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="max-height: 95vh;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; position: sticky; top: 0; z-index: 1050;">
                <h5 class="modal-title" id="createOrderModalLabel" style="font-weight: 600;">
                    <i class="fas fa-plus-circle me-2"></i>{{ __('client.create_new_order') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: calc(95vh - 120px); overflow-y: auto; padding: 25px;">
                <form id="createOrderForm">
                    @csrf
                    
                    <!-- Products Section - MOVED TO TOP -->
                    <div class="row mt-0 mb-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0" style="font-weight: 600;">
                                            <i class="fas fa-boxes me-2"></i>{{ __('client.products') }} <span class="text-warning">*</span>
                                        </h6>
                                        <button type="button" class="btn btn-light btn-sm" onclick="addProductRow()">
                                            <i class="fas fa-plus me-1"></i> {{ __('client.add_product') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="productsTable">
                                            <thead style="background: #f8f9fa;">
                                                <tr>
                                                    <th width="30%" style="border: none;">{{ __('client.product') }}</th>
                                                    <th width="15%" style="border: none;">{{ __('common.quantity') }}</th>
                                                    <th width="15%" style="border: none;">{{ __('client.unit_price') }}</th>
                                                    <th width="15%" style="border: none;">{{ __('client.discount') }}</th>
                                                    <th width="15%" style="border: none;">{{ __('common.total') }}</th>
                                                    <th width="10%" style="border: none;">{{ __('common.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="productRows">
                                                <!-- Product rows will be added here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Customer Information -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                                <div class="card-header" style="background: #e9ecef; border: none;">
                                    <h6 class="mb-0" style="color: #495057; font-weight: 600;">
                                        <i class="fas fa-user me-2"></i>{{ __('client.customer_information') }}
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="form-group mb-3">
                                        <label for="customer_id" class="form-label">{{ __('common.existing_customer') }}</label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="">{{ __('common.select_customer_or_create_new') }}</option>
                                            @foreach(auth('client')->user()->customers as $customer)
                                                <option value="{{ $customer->id }}" 
                                                        data-name="{{ $customer->name }}" 
                                                        data-phone="{{ $customer->phone }}" 
                                                        data-email="{{ $customer->email }}">
                                                    {{ $customer->name }} - {{ $customer->phone }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="customer_name" class="form-label">{{ __('common.customer_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_info[name]" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="customer_phone" class="form-label">{{ __('common.phone') }} <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="customer_phone" name="customer_info[phone]" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="customer_email" class="form-label">{{ __('common.email') }}</label>
                                                <input type="email" class="form-control" id="customer_email" name="customer_info[email]">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <label for="customer_address" class="form-label">{{ __('common.address') }} <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="customer_address" name="customer_info[address]" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment & Order Information -->
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                                <div class="card-header" style="background: #e9ecef; border: none;">
                                    <h6 class="mb-0" style="color: #495057; font-weight: 600;">
                                        <i class="fas fa-credit-card me-2"></i>{{ __('client.payment_method') }} & {{ __('common.notes') }}
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="form-group mb-3">
                                        <label for="payment_method" class="form-label">{{ __('client.payment_method') }}</label>
                                        <select class="form-select" id="payment_method" name="payment_method">
                                            <option value="cod" selected>{{ __('client.cash_on_delivery') }}</option>
                                            <option value="online">{{ __('client.online_payment') }}</option>
                                            <option value="bank_transfer">{{ __('client.bank_transfer') }}</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-0">
                                        <label for="notes" class="form-label">{{ __('common.notes') }}</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="{{ __('client.order_notes_placeholder') }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pricing Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card shadow-sm border-0">
                                <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; border: none;">
                                    <h6 class="mb-0" style="font-weight: 600;">
                                        <i class="fas fa-calculator me-2"></i>{{ __('client.shipping_charge') }}, {{ __('client.discount') }} & {{ __('client.advance_payment') }}
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="shipping_charge" class="form-label">{{ __('client.shipping_charge') }} (৳)</label>
                                                <input type="number" class="form-control" id="shipping_charge" name="shipping_charge" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="discount_type" class="form-label">{{ __('client.discount_type') }}</label>
                                                <select class="form-select" id="discount_type" name="discount_type">
                                                    <option value="fixed">{{ __('client.fixed_amount') }}</option>
                                                    <option value="percentage">{{ __('client.percentage') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="order_discount" class="form-label">{{ __('client.order_discount') }}</label>
                                                <input type="number" class="form-control" id="order_discount" name="discount_amount" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="advance_payment" class="form-label">{{ __('client.advance_payment') }} (৳)</label>
                                                <input type="number" class="form-control" id="advance_payment" name="advance_payment" min="0" step="0.01" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6;">
                                <div class="card-header" style="background: #343a40; color: white; border: none;">
                                    <h6 class="mb-0" style="font-weight: 600;">
                                        <i class="fas fa-calculator me-2"></i>{{ __('client.order_summary') }}
                                    </h6>
                                </div>
                                <div class="card-body" style="padding: 20px;">
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-sm mb-0" style="background: white; border-radius: 8px;">
                                                <tbody>
                                                    <tr>
                                                        <td style="border: none; padding: 8px 15px;"><strong>{{ __('client.subtotal') }}:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="orderSubtotal">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="orderDiscountRow" style="display: none;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>{{ __('client.order_discount') }}:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="orderDiscountAmount" class="text-danger">-৳0.00</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border: none; padding: 8px 15px;"><strong>{{ __('client.shipping') }}:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="shippingAmount">৳0.00</strong></td>
                                                    </tr>
                                                    <tr style="background: #e3f2fd;">
                                                        <td style="border: none; padding: 12px 15px; border-top: 2px solid #2196f3;"><strong>{{ __('client.total_amount') }}:</strong></td>
                                                        <td style="border: none; padding: 12px 15px; border-top: 2px solid #2196f3;" class="text-end"><strong id="totalAmount" class="text-primary" style="font-size: 1.1em;">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="advancePaymentRow" style="display: none; background: #e8f5e8;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>{{ __('client.advance_payment') }}:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="advanceAmount" class="text-success">৳0.00</strong></td>
                                                    </tr>
                                                    <tr id="remainingRow" style="display: none; background: #fff3cd;">
                                                        <td style="border: none; padding: 8px 15px;"><strong>{{ __('client.remaining_amount') }}:</strong></td>
                                                        <td style="border: none; padding: 8px 15px;" class="text-end"><strong id="remainingAmount" class="text-warning">৳0.00</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border: none; padding: 20px 25px;">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('client.all_required_fields_must_be_filled') }}
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>{{ __('common.cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary" onclick="createOrder()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;" id="createOrderButton">
                            <i class="fas fa-save me-1"></i> {{ __('client.create_order') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.edit_order') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

@push('styles')
<style>
/* Enhanced modal styling */
.modal-xl {
    max-width: 95% !important;
    width: 95% !important;
}

/* Disabled button styling */
#createOrderButton:disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
}

@media (min-width: 1200px) {
    .modal-xl {
        max-width: 1200px !important;
        width: 1200px !important;
    }
}

@media (min-width: 992px) and (max-width: 1199px) {
    .modal-xl {
        max-width: 90% !important;
        width: 90% !important;
    }
}

@media (max-width: 991px) {
    .modal-xl {
        max-width: 95% !important;
        width: 95% !important;
        margin: 10px auto !important;
    }
    
    .modal-content {
        min-height: auto !important;
        max-height: 90vh !important;
    }
    
    .modal-body {
        max-height: calc(90vh - 120px) !important;
    }
}

/* Form improvements */
.form-select, .form-control {
    border-radius: 8px !important;
    border: 1px solid #e0e0e0 !important;
    padding: 10px 15px !important;
    font-size: 14px !important;
}

.form-select:focus, .form-control:focus {
    border-color: #667eea !important;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25) !important;
}

.form-label {
    font-weight: 600 !important;
    color: #495057 !important;
    margin-bottom: 8px !important;
}

/* Product table improvements */
#productsTable input, #productsTable select {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 13px;
    width: 100%;
}

#productsTable .table-responsive {
    border-radius: 8px;
}

#productsTable th {
    font-weight: 600;
    color: #495057;
    background: #f8f9fa !important;
    padding: 12px 8px;
}

#productsTable .product-total {
    font-weight: 600;
    color: #28a745;
    font-size: 14px;
}

/* Card improvements */
.card {
    border-radius: 12px !important;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08) !important;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

/* Animation improvements */
.modal.fade .modal-dialog {
    transition: transform 0.4s ease-out, opacity 0.3s ease-out;
    transform: scale(0.9) translateY(-50px);
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Scrollbar styling */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}
</style>
@endpush

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
    
    // Disable the create order button and show loading state
    const createButton = $('#createOrderButton');
    const originalText = createButton.html();
    createButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> {{ __("client.creating_order") }}...');
    
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
            location.reload();
        },
        error: function(xhr) {
            // Restore button state
            createButton.prop('disabled', false).html(originalText);
            
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
        },
        complete: function() {
            // Always restore button state when request completes (backup)
            if (createButton.prop('disabled')) {
                createButton.prop('disabled', false).html(originalText);
            }
        }
    });
}

function updateOrderStatusIndex(orderId, status) {
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