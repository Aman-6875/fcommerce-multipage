<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'client_id',
        'customer_id',
        'service_type',
        'service_name',
        'booking_date',
        'booking_time',
        'duration',
        'service_price',
        'customer_info',
        'location_info',
        'status',
        'notes',
        // New fields for client panel
        'image_url',
        'service_link',
        'category',
        'tags',
        'is_active',
        'duration_hours',
        'available_days',
        'start_time',
        'end_time',
        'max_bookings_per_day',
        'advance_booking_days',
        'service_areas',
        'cancellation_policy',
        'sort_order'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'booking_time' => 'datetime:H:i',
        'service_price' => 'decimal:2',
        'customer_info' => 'array',
        'location_info' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'available_days' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'service_areas' => 'array'
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAvailableOnDay($query, $dayName)
    {
        return $query->whereJsonContains('available_days', strtolower($dayName));
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '৳' . number_format($this->service_price, 2);
    }

    public function getDurationDisplayAttribute()
    {
        if ($this->duration_hours == 1) {
            return '1 hour';
        }
        return $this->duration_hours . ' hours';
    }

    public function getAvailabilityDisplayAttribute()
    {
        if (!$this->available_days) {
            return 'Not specified';
        }
        
        $days = array_map('ucfirst', $this->available_days);
        return implode(', ', $days);
    }

    public function getTimeRangeDisplayAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
        }
        return 'Any time';
    }

    // Check if service is available on a specific date and time
    public function isAvailableAt($date, $time)
    {
        $dayOfWeek = strtolower($date->format('l'));
        
        // Check if available on this day
        if ($this->available_days && !in_array($dayOfWeek, $this->available_days)) {
            return false;
        }
        
        // Check time range
        if ($this->start_time && $this->end_time) {
            $timeObj = \Carbon\Carbon::createFromFormat('H:i', $time);
            if ($timeObj->lt($this->start_time) || $timeObj->gt($this->end_time)) {
                return false;
            }
        }
        
        // Check booking limit for that day
        $existingBookings = $this->bookings()
            ->where('booking_date', $date->format('Y-m-d'))
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();
            
        return $existingBookings < $this->max_bookings_per_day;
    }
}
