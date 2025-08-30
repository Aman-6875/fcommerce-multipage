<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class PublicInvoiceController extends Controller
{
    public function show($hash)
    {
        $orderId = Hashids::decode($hash);

        if (empty($orderId)) {
            abort(404);
        }

        $order = Order::with(['customer', 'orderMeta.product', 'client', 'facebookPage'])->find($orderId[0]);

        if (!$order) {
            abort(404);
        }

        // Get invoice settings from client preferences
        $invoiceSettings = $this->getInvoiceSettings($order->client);

        // Determine format
        $format = 'a4'; // a4, thermal, pdf, print
        $language = $invoiceSettings['language'] ?? 'en';

        // Set app locale for this request
        app()->setLocale($language);

        // Currency symbol
        $currencySymbol = $invoiceSettings['currency_symbol'] ?? '৳';

        // Facebook page info
        $facebookPage = $order->facebookPage;

        $data = compact('order', 'invoiceSettings', 'format', 'language', 'currencySymbol', 'facebookPage');

        return view('client.orders.invoice', $data);
    }

    private function getInvoiceSettings($client): array
    {
        // Default settings - in real implementation, these would come from client settings table
        return [
            'title' => 'INVOICE',
            'language' => 'en',
            'currency_symbol' => '৳',
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'text_color' => '#333333',
            'border_color' => '#dee2e6',
            'font_family' => "'Helvetica Neue', Arial, sans-serif",
            'show_page_info' => true,
            'show_contact_info' => true,
            'show_sku' => true,
            'show_qr_code' => true,
            'business_phone' => $client->phone ?? '',
            'business_email' => $client->email ?? '',
            'business_address' => $client->address ?? '',
            'payment_instructions' => 'Please keep this invoice for your records.',
            'default_notes' => 'Thank you for choosing our service. Your satisfaction is our priority.',
            'footer_text' => 'Generated from Facebook Messenger Commerce System',
        ];
    }
}
