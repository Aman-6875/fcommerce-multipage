<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'client_id',
        'customer_id',
        'service_id',
        'booking_number',
        'booking_date',
        'booking_time',
        'end_time',
        'service_price',
        'total_amount',
        'customer_info',
        'booking_details',
        'location_info',
        'status',
        'payment_status',
        'notes',
        'customer_notes',
        'confirmed_at',
        'completed_at',
        'cancellation_info'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'service_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'customer_info' => 'array',
        'booking_details' => 'array',
        'location_info' => 'array',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancellation_info' => 'array'
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now()->toDateString());
    }

    public function scopeToday($query)
    {
        return $query->where('booking_date', now()->toDateString());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors
    public function getFormattedTotalAttribute()
    {
        return '৳' . number_format($this->total_amount, 2);
    }

    public function getIsUpcomingAttribute()
    {
        return $this->booking_date >= now()->toDateString();
    }

    public function getIsTodayAttribute()
    {
        return $this->booking_date == now()->toDateString();
    }

    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']) && $this->is_upcoming;
    }

    // Generate booking number
    public static function generateBookingNumber()
    {
        $year = date('Y');
        $lastBooking = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNumber = $lastBooking ? 
            (int) substr($lastBooking->booking_number, -6) + 1 : 1;
            
        return 'BKG-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
