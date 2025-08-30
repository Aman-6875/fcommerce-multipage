<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Client $client;
    protected array $filters;

    public function __construct(Client $client, array $filters = [])
    {
        $this->client = $client;
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Order::where('client_id', $this->client->id)
            ->with(['customer', 'orderMeta.product']);

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer Name',
            'Customer Phone',
            'Products',
            'Quantities',
            'Subtotal',
            'Discount',
            'Shipping',
            'Advance Payment',
            'Total Amount',
            'Status',
            'Payment Method',
            'Order Date',
            'Confirmed At',
            'Shipped At',
            'Delivered At',
        ];
    }

    public function map($order): array
    {
        $products = $order->orderMeta->pluck('product_name')->join(', ');
        $quantities = $order->orderMeta->pluck('quantity')->join(', ');
        
        return [
            $order->order_number,
            $order->customer_info['name'] ?? 'N/A',
            $order->customer_info['phone'] ?? 'N/A',
            $products,
            $quantities,
            '৳' . number_format($order->subtotal, 2),
            '৳' . number_format($order->discount_amount, 2),
            '৳' . number_format($order->shipping_charge, 2),
            '৳' . number_format($order->advance_payment, 2),
            '৳' . number_format($order->total_amount, 2),
            ucfirst($order->status),
            ucfirst($order->payment_method),
            $order->created_at->format('Y-m-d H:i:s'),
            $order->confirmed_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $order->shipped_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $order->delivered_at?->format('Y-m-d H:i:s') ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}