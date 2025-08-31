<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpgradeRequest extends Model
{
    protected $fillable = [
        'client_id',
        'current_plan',
        'requested_plan',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_proof',
        'notes',
        'status',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    public static function getPlanPrices(): array
    {
        return [
            'premium' => [
                'monthly' => 1000,
                'yearly' => 10000,
                'name' => 'Premium Plan'
            ],
            'enterprise' => [
                'monthly' => 2500,
                'yearly' => 25000,
                'name' => 'Enterprise Plan'
            ]
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            'bkash' => 'bKash',
            'nagad' => 'Nagad',
            'rocket' => 'Rocket',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash'
        ];
    }
}
