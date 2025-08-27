<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'client_id',
        'facebook_page_id',
        'name',
        'description',
        'sku',
        'price',
        'sale_price',
        'stock_quantity',
        'image_url',
        'product_link',
        'category',
        'tags',
        'is_active',
        'track_stock',
        'weight',
        'specifications',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'track_stock' => 'boolean',
        'tags' => 'array',
        'specifications' => 'array',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '৳' . number_format($this->price, 2);
    }

    public function getFormattedSalePriceAttribute()
    {
        if ($this->sale_price) {
            return '৳' . number_format($this->sale_price, 2);
        }
        return null;
    }

    public function getEffectivePriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    public function getStockStatusAttribute()
    {
        if (!$this->track_stock) {
            return 'unlimited';
        }
        
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity <= 5) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}
