<!DOCTYPE html>
<html lang="{{ $language ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $invoiceSettings['title'] ?? __('client.invoice') }} - {{ $order->order_number }}</title>
    <style>
        :root {
            --primary-color: {{ $invoiceSettings['primary_color'] ?? '#007bff' }};
            --secondary-color: {{ $invoiceSettings['secondary_color'] ?? '#6c757d' }};
            --text-color: {{ $invoiceSettings['text_color'] ?? '#333333' }};
            --border-color: {{ $invoiceSettings['border_color'] ?? '#dee2e6' }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $invoiceSettings['font_family'] ?? "'Helvetica Neue', Arial, sans-serif" }};
            font-size: {{ $format === 'thermal' ? '12px' : '14px' }};
            line-height: 1.4;
            color: var(--text-color);
            background: white;
        }

        .invoice-container {
            max-width: {{ $format === 'thermal' ? '80mm' : '210mm' }};
            margin: 0 auto;
            padding: {{ $format === 'thermal' ? '5mm' : '20mm' }};
            background: white;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: {{ $format === 'thermal' ? '15px' : '30px' }};
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: {{ $format === 'thermal' ? '10px' : '20px' }};
        }

        .business-info {
            flex: 1;
        }

        .business-logo {
            max-width: {{ $format === 'thermal' ? '40px' : '80px' }};
            height: auto;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .business-name {
            font-size: {{ $format === 'thermal' ? '16px' : '24px' }};
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .business-details {
            font-size: {{ $format === 'thermal' ? '10px' : '12px' }};
            color: var(--secondary-color);
            line-height: 1.3;
        }

        .invoice-title {
            text-align: right;
            font-size: {{ $format === 'thermal' ? '18px' : '28px' }};
            font-weight: bold;
            color: var(--primary-color);
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: {{ $format === 'thermal' ? '15px' : '25px' }};
            @if($format === 'thermal') flex-direction: column; @endif
        }

        .bill-to, .invoice-details {
            @if($format === 'thermal') margin-bottom: 10px; @else flex: 1; @endif
        }

        .bill-to {
            @if($format !== 'thermal') margin-right: 20px; @endif
        }

        .section-title {
            font-size: {{ $format === 'thermal' ? '12px' : '14px' }};
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .customer-info, .order-info {
            font-size: {{ $format === 'thermal' ? '10px' : '12px' }};
            line-height: 1.4;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: {{ $format === 'thermal' ? '15px' : '25px' }};
        }

        .items-table th, .items-table td {
            border: 1px solid var(--border-color);
            padding: {{ $format === 'thermal' ? '4px 2px' : '8px 10px' }};
            text-align: left;
            font-size: {{ $format === 'thermal' ? '9px' : '12px' }};
        }

        .items-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .items-table .text-center { text-align: center; }
        .items-table .text-right { text-align: right; }

        .total-section {
            margin-left: auto;
            width: {{ $format === 'thermal' ? '100%' : '50%' }};
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-table td {
            padding: {{ $format === 'thermal' ? '3px 5px' : '6px 10px' }};
            font-size: {{ $format === 'thermal' ? '10px' : '12px' }};
            border-bottom: 1px solid var(--border-color);
        }

        .total-table .total-row {
            font-weight: bold;
            font-size: {{ $format === 'thermal' ? '12px' : '14px' }};
            border-top: 2px solid var(--primary-color);
            background-color: #f8f9fa;
        }

        .total-table .text-right { text-align: right; }

        .payment-info {
            margin-top: {{ $format === 'thermal' ? '15px' : '25px' }};
            padding: {{ $format === 'thermal' ? '8px' : '15px' }};
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
        }

        .notes {
            margin-top: {{ $format === 'thermal' ? '10px' : '20px' }};
            padding: {{ $format === 'thermal' ? '8px' : '15px' }};
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
        }

        .footer {
            margin-top: {{ $format === 'thermal' ? '15px' : '30px' }};
            text-align: center;
            font-size: {{ $format === 'thermal' ? '9px' : '11px' }};
            color: var(--secondary-color);
            border-top: 1px solid var(--border-color);
            padding-top: {{ $format === 'thermal' ? '8px' : '15px' }};
        }

        .qr-code {
            float: right;
            margin-left: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: {{ $format === 'thermal' ? '2px 6px' : '4px 8px' }};
            border-radius: 12px;
            font-size: {{ $format === 'thermal' ? '9px' : '10px' }};
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #e2e3e5; color: #383d41; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        @media print {
            body { print-color-adjust: exact; }
            .invoice-container { 
                max-width: none; 
                margin: 0; 
                padding: {{ $format === 'thermal' ? '2mm' : '10mm' }}; 
            }
        }

        @if($format === 'thermal')
        .items-table th:nth-child(4), 
        .items-table td:nth-child(4) { display: none; } /* Hide discount column on thermal */
        .invoice-meta { flex-direction: column; }
        .bill-to, .invoice-details { width: 100%; }
        @endif
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="business-info">
                @if($facebookPage->page_picture)
                    <img src="{{ $facebookPage->page_picture }}" alt="{{ $facebookPage->page_name }}" class="business-logo">
                @endif
                <div class="business-name">{{ $facebookPage->page_name }}</div>
                <div class="business-details">
                    @if($invoiceSettings['show_page_info'] ?? true)
                        <div>{{ __('client.facebook_page') }}: {{ $facebookPage->page_name }}</div>
                        @if($invoiceSettings['show_contact_info'] ?? true)
                            @if($invoiceSettings['business_phone'])
                                <div>{{ __('common.phone') }}: {{ $invoiceSettings['business_phone'] }}</div>
                            @endif
                            @if($invoiceSettings['business_email'])
                                <div>{{ __('common.email') }}: {{ $invoiceSettings['business_email'] }}</div>
                            @endif
                            @if($invoiceSettings['business_address'])
                                <div>{{ __('common.address') }}: {{ $invoiceSettings['business_address'] }}</div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
            <div class="invoice-title">
                {{ $invoiceSettings['title'] ?? __('client.invoice') }}
                @if($order->tracking_token && ($invoiceSettings['show_qr_code'] ?? true))
                    <div class="qr-code">
                        {{-- QR Code temporarily disabled - install simplesoftwareio/simple-qrcode package to enable --}}
                        <div style="width: 60px; height: 60px; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">
                            QR Code
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="bill-to">
                <div class="section-title">{{ __('client.bill_to') }}</div>
                <div class="customer-info">
                    <strong>{{ $order->customer_info['name'] ?? 'N/A' }}</strong><br>
                    {{ __('common.phone') }}: {{ $order->customer_info['phone'] ?? 'N/A' }}<br>
                    @if(isset($order->customer_info['email']) && $order->customer_info['email'])
                        {{ __('common.email') }}: {{ $order->customer_info['email'] }}<br>
                    @endif
                    @if(isset($order->customer_info['address']) && $order->customer_info['address'])
                        {{ __('common.address') }}: {{ $order->customer_info['address'] }}
                    @endif
                </div>
            </div>
            <div class="invoice-details">
                <div class="section-title">{{ __('client.order_details') }}</div>
                <div class="order-info">
                    <div>{{ __('client.order_number') }}: <strong>{{ $order->order_number }}</strong></div>
                    @if($order->invoice_number)
                        <div>{{ __('client.invoice_number') }}: <strong>{{ $order->invoice_number }}</strong></div>
                    @endif
                    <div>{{ __('common.date') }}: {{ $order->created_at->format('d M, Y') }}</div>
                    <div>{{ __('common.status') }}: 
                        <span class="status-badge status-{{ $order->status }}">
                            {{ __('common.' . $order->status) }}
                        </span>
                    </div>
                    @if($order->confirmed_at && in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']))
                        <div>{{ __('common.confirmed_at') }}: {{ $order->confirmed_at->format('d M, Y') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: {{ $format === 'thermal' ? '45%' : '40%' }}">{{ __('client.item') }}</th>
                    <th style="width: {{ $format === 'thermal' ? '15%' : '12%' }}" class="text-center">{{ __('common.qty') }}</th>
                    <th style="width: {{ $format === 'thermal' ? '20%' : '15%' }}" class="text-right">{{ __('client.price') }}</th>
                    @if($format !== 'thermal')
                        <th style="width: 15%" class="text-right">{{ __('client.discount') }}</th>
                    @endif
                    <th style="width: {{ $format === 'thermal' ? '20%' : '18%' }}" class="text-right">{{ __('common.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderMeta as $meta)
                <tr>
                    <td>
                        <strong>{{ $meta->product_name }}</strong>
                        @if($meta->product_sku && ($invoiceSettings['show_sku'] ?? true))
                            <br><small style="color: var(--secondary-color);">{{ __('client.sku') }}: {{ $meta->product_sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $meta->quantity }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($meta->unit_price, 2) }}</td>
                    @if($format !== 'thermal')
                        <td class="text-right">
                            @if($meta->discount_amount > 0)
                                -{{ $currencySymbol }}{{ number_format($meta->discount_amount, 2) }}
                            @else
                                -
                            @endif
                        </td>
                    @endif
                    <td class="text-right"><strong>{{ $currencySymbol }}{{ number_format($meta->total_price, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td>{{ __('client.subtotal') }}:</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($order->orderMeta->sum('total_price'), 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                <tr>
                    <td>{{ __('client.order_discount') }} ({{ ucfirst($order->discount_type) }}):</td>
                    <td class="text-right" style="color: #dc3545;">-{{ $currencySymbol }}{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->shipping_charge > 0)
                <tr>
                    <td>{{ __('client.shipping_charge') }}:</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($order->shipping_charge, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td><strong>{{ __('client.total_amount') }}:</strong></td>
                    <td class="text-right"><strong>{{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}</strong></td>
                </tr>
                @if($order->advance_payment > 0)
                <tr>
                    <td>{{ __('client.advance_payment') }}:</td>
                    <td class="text-right" style="color: #28a745;">{{ $currencySymbol }}{{ number_format($order->advance_payment, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>{{ __('client.remaining_amount') }}:</strong></td>
                    <td class="text-right"><strong style="color: #ffc107;">{{ $currencySymbol }}{{ number_format($order->total_amount - $order->advance_payment, 2) }}</strong></td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <div class="section-title">{{ __('client.payment_information') }}</div>
            <div>
                {{ __('client.payment_method') }}: 
                <strong>
                    @switch($order->payment_method)
                        @case('cod')
                            {{ __('client.cash_on_delivery') }}
                            @break
                        @case('online')
                            {{ __('client.online_payment') }}
                            @break
                        @case('bank_transfer')
                            {{ __('client.bank_transfer') }}
                            @break
                        @default
                            {{ ucfirst($order->payment_method) }}
                    @endswitch
                </strong>
            </div>
            @if($invoiceSettings['payment_instructions'])
                <div style="margin-top: 8px; font-size: {{ $format === 'thermal' ? '9px' : '11px' }};">
                    {{ $invoiceSettings['payment_instructions'] }}
                </div>
            @endif
        </div>

        <!-- Notes -->
        @if($order->notes || $invoiceSettings['default_notes'])
        <div class="notes">
            <div class="section-title">{{ __('common.notes') }}</div>
            <div style="font-size: {{ $format === 'thermal' ? '9px' : '11px' }};">
                @if($order->notes)
                    {{ $order->notes }}
                @endif
                @if($invoiceSettings['default_notes'])
                    @if($order->notes)<br><br>@endif
                    {{ $invoiceSettings['default_notes'] }}
                @endif
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            @if($invoiceSettings['footer_text'])
                <div>{{ $invoiceSettings['footer_text'] }}</div>
            @else
                <div>{{ __('client.thank_you_for_business') }}</div>
            @endif
            @if($order->tracking_token)
                <div style="margin-top: 5px;">
                    {{ __('client.track_order') }}: {{ url('/track/' . $order->tracking_token) }}
                </div>
            @endif
            <div style="margin-top: 8px; font-size: {{ $format === 'thermal' ? '8px' : '10px' }};">
                {{ __('client.generated_on') }}: {{ now()->format('d M, Y h:i A') }}
            </div>
        </div>
    </div>

    @if($format === 'print')
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    @endif
</body>
</html>