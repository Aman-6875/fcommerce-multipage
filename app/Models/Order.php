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
        'order_number',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
        'customer_info',
        'delivery_info',
        'status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'customer_info' => 'array',
        'delivery_info' => 'array',
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
}
