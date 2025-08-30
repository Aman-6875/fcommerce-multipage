@extends('layouts.client')

@section('title', __('client.order_details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">{{ __('client.order_details') }} - #{{ $order->order_number }}</h4>
                        <small class="text-muted">{{ __('common.created_at') }}: {{ $order->created_at->format('d M, Y h:i A') }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        @if(in_array($order->status, ['pending', 'confirmed']))
                            <button class="btn btn-warning btn-sm" onclick="editOrder({{ $order->id }})">
                                <i class="fas fa-edit"></i> {{ __('common.edit') }}
                            </button>
                        @endif
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                {{ __('common.actions') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('client.orders.invoice', $order) }}" target="_blank">
                                    <i class="fas fa-print"></i> {{ __('client.print_invoice') }}
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('client.orders.invoice', ['order' => $order, 'format' => 'pdf']) }}" target="_blank">
                                    <i class="fas fa-download"></i> {{ __('client.download_pdf') }}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="sendInvoiceToCustomer({{ $order->id }})">
                                    <i class="fab fa-facebook-messenger"></i> {{ __('client.send_to_customer') }}
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('client.orders.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Order Status & Info -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('client.order_information') }}</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>{{ __('client.order_number') }}:</strong></td>
                                            <td>{{ $order->order_number }}</td>
                                        </tr>
                                        @if($order->invoice_number)
                                        <tr>
                                            <td><strong>{{ __('client.invoice_number') }}:</strong></td>
                                            <td>{{ $order->invoice_number }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>{{ __('common.status') }}:</strong></td>
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
                                                @endswitch
                                                <div class="mt-2">
                                                    <select class="form-control form-control-sm" onchange="updateOrderStatus({{ $order->id }}, this.value)">
                                                        <option value="">{{ __('client.change_status') }}...</option>
                                                        @if($order->status === 'pending')
                                                            <option value="confirmed">{{ __('common.confirmed') }}</option>
                                                            <option value="cancelled">{{ __('common.cancelled') }}</option>
                                                        @endif
                                                        @if($order->status === 'confirmed')
                                                            <option value="processing">{{ __('common.processing') }}</option>
                                                            <option value="cancelled">{{ __('common.cancelled') }}</option>
                                                        @endif
                                                        @if($order->status === 'processing')
                                                            <option value="shipped">{{ __('common.shipped') }}</option>
                                                            <option value="cancelled">{{ __('common.cancelled') }}</option>
                                                        @endif
                                                        @if($order->status === 'shipped')
                                                            <option value="delivered">{{ __('common.delivered') }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('client.payment_method') }}:</strong></td>
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
                                                @endswitch
                                            </td>
                                        </tr>
                                        @if($order->confirmed_at)
                                        <tr>
                                            <td><strong>{{ __('common.confirmed_at') }}:</strong></td>
                                            <td>{{ $order->confirmed_at->format('d M, Y h:i A') }}</td>
                                        </tr>
                                        @endif
                                        @if($order->shipped_at)
                                        <tr>
                                            <td><strong>{{ __('common.shipped_at') }}:</strong></td>
                                            <td>{{ $order->shipped_at->format('d M, Y h:i A') }}</td>
                                        </tr>
                                        @endif
                                        @if($order->delivered_at)
                                        <tr>
                                            <td><strong>{{ __('common.delivered_at') }}:</strong></td>
                                            <td>{{ $order->delivered_at->format('d M, Y h:i A') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('client.customer_information') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($order->customer)
                                        <div class="mb-3">
                                            <strong>{{ __('common.linked_customer') }}:</strong><br>
                                            <a href="#" class="text-decoration-none">{{ $order->customer->name }}</a>
                                        </div>
                                    @endif
                                    
                                    @if($order->customer_info)
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td><strong>{{ __('common.name') }}:</strong></td>
                                                <td>{{ $order->customer_info['name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>{{ __('common.phone') }}:</strong></td>
                                                <td>{{ $order->customer_info['phone'] ?? 'N/A' }}</td>
                                            </tr>
                                            @if(isset($order->customer_info['email']) && $order->customer_info['email'])
                                            <tr>
                                                <td><strong>{{ __('common.email') }}:</strong></td>
                                                <td>{{ $order->customer_info['email'] }}</td>
                                            </tr>
                                            @endif
                                            @if(isset($order->customer_info['address']) && $order->customer_info['address'])
                                            <tr>
                                                <td><strong>{{ __('common.address') }}:</strong></td>
                                                <td>{{ $order->customer_info['address'] }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('client.order_summary') }}</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td>{{ __('client.subtotal') }}:</td>
                                            <td class="text-end">৳{{ number_format($order->orderMeta->sum('total_price'), 2) }}</td>
                                        </tr>
                                        @if($order->discount_amount > 0)
                                        <tr>
                                            <td>{{ __('client.order_discount') }} ({{ ucfirst($order->discount_type) }}):</td>
                                            <td class="text-end text-danger">-৳{{ number_format($order->discount_amount, 2) }}</td>
                                        </tr>
                                        @endif
                                        @if($order->shipping_charge > 0)
                                        <tr>
                                            <td>{{ __('client.shipping_charge') }}:</td>
                                            <td class="text-end">৳{{ number_format($order->shipping_charge, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="fw-bold border-top">
                                            <td><strong>{{ __('client.total_amount') }}:</strong></td>
                                            <td class="text-end"><strong>৳{{ number_format($order->total_amount, 2) }}</strong></td>
                                        </tr>
                                        @if($order->advance_payment > 0)
                                        <tr>
                                            <td>{{ __('client.advance_payment') }}:</td>
                                            <td class="text-end text-success">৳{{ number_format($order->advance_payment, 2) }}</td>
                                        </tr>
                                        <tr class="fw-bold">
                                            <td><strong>{{ __('client.remaining_amount') }}:</strong></td>
                                            <td class="text-end"><strong class="text-warning">৳{{ number_format($order->total_amount - $order->advance_payment, 2) }}</strong></td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('client.order_items') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('client.product') }}</th>
                                                    <th>{{ __('client.sku') }}</th>
                                                    <th class="text-center">{{ __('common.quantity') }}</th>
                                                    <th class="text-end">{{ __('client.unit_price') }}</th>
                                                    <th class="text-end">{{ __('client.discount') }}</th>
                                                    <th class="text-end">{{ __('common.total') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($order->orderMeta as $meta)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $meta->product_name }}</strong>
                                                            @if($meta->product)
                                                                <br><small class="text-muted">{{ $meta->product->category }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>{{ $meta->product_sku ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $meta->quantity }}</td>
                                                    <td class="text-end">৳{{ number_format($meta->unit_price, 2) }}</td>
                                                    <td class="text-end">
                                                        @if($meta->discount_amount > 0)
                                                            <span class="text-danger">-৳{{ number_format($meta->discount_amount, 2) }}</span>
                                                        @else
                                                            ৳0.00
                                                        @endif
                                                    </td>
                                                    <td class="text-end"><strong>৳{{ number_format($meta->total_price, 2) }}</strong></td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">{{ __('client.no_products_found') }}</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($order->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('common.notes') }}</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $order->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateOrderStatus(orderId, newStatus) {
    if (!newStatus) return;
    
    if(confirm(`Are you sure you want to change the order status to ${newStatus}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/client/orders/${orderId}/status`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.innerHTML = `
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="status" value="${newStatus}">
        `;
        
        document.body.appendChild(form);
        form.submit();
    }
}

function editOrder(orderId) {
    window.location.href = `/client/orders/${orderId}/edit`;
}

function sendInvoiceToCustomer(orderId) {
    if(confirm('{{ __("client.send_invoice_confirmation") }}')) {
        $.ajax({
            url: `/client/orders/${orderId}/send-invoice`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                alert('{{ __("client.invoice_sent_successfully") }}');
            },
            error: function(xhr) {
                alert('{{ __("common.error_occurred") }}: ' + (xhr.responseJSON?.message || xhr.responseText));
            }
        });
    }
}
</script>
@endpush