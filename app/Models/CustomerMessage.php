<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'page_customer_id',
        'client_id',
        'message_type',
        'message_content',
        'attachments',
        'message_data',
        'is_read',
        'response_time',
    ];

    protected $casts = [
        'attachments' => 'array',
        'message_data' => 'array',
        'is_read' => 'boolean',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pageCustomer()
    {
        return $this->belongsTo(PageCustomer::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeIncoming($query)
    {
        return $query->where('message_type', 'incoming');
    }

    public function scopeOutgoing($query)
    {
        return $query->where('message_type', 'outgoing');
    }

    public function scopeAutomated($query)
    {
        return $query->where('message_type', 'automated');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Helper methods
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function isIncoming(): bool
    {
        return $this->message_type === 'incoming';
    }

    public function isOutgoing(): bool
    {
        return $this->message_type === 'outgoing';
    }

    public function isAutomated(): bool
    {
        return $this->message_type === 'automated';
    }
}
