<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_id', 
        'facebook_user_id',
        'facebook_page_id',
        'order_number',
        'invoice_number',
        'total_amount',
        'subtotal',
        'shipping_charge',
        'shipping_zone',
        'minimum_order_amount',
        'maximum_order_amount',
        'customer_info',
        'delivery_info',
        'status',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'customer_info' => 'array',
        'delivery_info' => 'array',
        'product_selections' => 'array',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function conversationState()
    {
        return $this->belongsTo(ConversationState::class);
    }

    public function orderMeta()
    {
        return $this->hasMany(OrderMeta::class);
    }

    public function facebookPage()
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Helper method to generate order numbers
    public static function generateOrderNumber(int $clientId): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('client_id', $clientId)
            ->where('created_at', '>=', now()->startOfDay())
            ->latest()
            ->first();

        $sequence = $lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1;
        
        return $date . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // Helper methods for working with products
    public function addProduct(Product $product, int $quantity = 1, ?array $productSnapshot = null): OrderMeta
    {
        $unitPrice = $product->sale_price ?: $product->price;
        $totalPrice = $unitPrice * $quantity;

        return $this->orderMeta()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'product_snapshot' => $productSnapshot ?: [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'category' => $product->category,
                'image_url' => $product->image_url
            ]
        ]);
    }

    public function getProductsSubtotal(): float
    {
        return $this->orderMeta->sum('total_price');
    }

    public function updateTotalAmount(): void
    {
        $subtotal = $this->getProductsSubtotal();
        $total = $subtotal + $this->shipping_charge;
        
        $this->update(['total_amount' => $total]);
    }

    public function getProductsList(): array
    {
        return $this->orderMeta->map(function ($meta) {
            return [
                'id' => $meta->product_id,
                'name' => $meta->product_name,
                'sku' => $meta->product_sku,
                'quantity' => $meta->quantity,
                'unit_price' => $meta->unit_price,
                'total_price' => $meta->total_price,
            ];
        })->toArray();
    }
}
