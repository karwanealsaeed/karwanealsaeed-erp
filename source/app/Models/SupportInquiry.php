<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportInquiry extends Model
{
    protected $fillable = [
        'inquiry_no',
        'name',
        'mobile',
        'email',
        'service_type',
        'subject',
        'message',
        'smart_reply',
        'status',
        'admin_notes',
        'contacted_at',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportInquiry $inquiry): void {
            if (blank($inquiry->inquiry_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $inquiry->inquiry_no = 'SI-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }

            if (blank($inquiry->status)) {
                $inquiry->status = 'New';
            }

            if (blank($inquiry->smart_reply)) {
                $inquiry->smart_reply = self::makeSmartReply($inquiry->service_type, $inquiry->name);
            }
        });
    }

    public static function makeSmartReply(?string $serviceType, ?string $name): string
    {
        $customerName = filled($name) ? $name : 'Dear Customer';
        $service = filled($serviceType) ? $serviceType : 'Travel Service';

        return "Assalam o Alaikum {$customerName}, thank you for contacting Karwan E Al Saeed Travel & Tours regarding {$service}. Our team will contact you shortly. For urgent support, please WhatsApp us.";
    }
}