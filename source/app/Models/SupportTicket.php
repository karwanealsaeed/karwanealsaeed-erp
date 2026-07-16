<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'customer_id',
        'name',
        'mobile',
        'email',
        'service_type',
        'subject',
        'message',
        'priority',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket): void {
            if (blank($ticket->ticket_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $ticket->ticket_no = 'ST-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }
}