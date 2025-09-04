<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'facebook_page_id',
        'customer_id',
        'inquiry_number',
        'inquiry_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'service_name',
        'description',
        'preferred_date',
        'preferred_time',
        'budget_range',
        'quantity',
        'extra_fields',
        'status',
        'language',
        'admin_notes',
        'priority'
    ];

    protected $casts = [
        'extra_fields' => 'array',
        'preferred_date' => 'date',
        'preferred_time' => 'time'
    ];

    // Relationships
    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Helper methods
    public static function generateInquiryNumber(string $inquiryType = 'inquiry'): string
    {
        $prefix = match($inquiryType) {
            'order' => 'ORD',
            'booking' => 'BOK',
            'appointment' => 'APT',
            'consultation' => 'CON',
            'reservation' => 'RES',
            'quote' => 'QUO',
            'purchase' => 'PUR',
            default => 'INQ'
        };

        $year = date('Y');
        $lastNumber = static::where('inquiry_number', 'like', $prefix . $year . '%')
            ->orderByDesc('inquiry_number')
            ->value('inquiry_number');

        if ($lastNumber) {
            $lastNum = (int)substr($lastNumber, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return $prefix . $year . $newNum;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'contacted' => '<span class="badge bg-info">Contacted</span>',
            'confirmed' => '<span class="badge bg-success">Confirmed</span>',
            'completed' => '<span class="badge bg-primary">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => '<span class="badge bg-danger">Urgent</span>',
            'high' => '<span class="badge bg-warning">High</span>',
            'medium' => '<span class="badge bg-info">Medium</span>',
            'low' => '<span class="badge bg-secondary">Low</span>',
            default => '<span class="badge bg-secondary">Medium</span>'
        };
    }

    // Scopes
    public function scopeForPage($query, int $facebookPageId)
    {
        return $query->where('facebook_page_id', $facebookPageId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $inquiryType)
    {
        return $query->where('inquiry_type', $inquiryType);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }
}