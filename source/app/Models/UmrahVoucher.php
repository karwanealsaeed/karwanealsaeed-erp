<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmrahVoucher extends Model
{
    protected $fillable = [
        'voucher_no',
        'customer_id',
        'umrah_booking_id',
        'umrah_package_id',
        'lead_guest',
        'phone',
        'adults',
        'children',
        'infants',
        'passengers',
        'room_type',
        'room_count',
        'pnr',
        'booking_reference',
        'remarks',
        'special_instructions',
        'terms_conditions',
        'status',
    ];

    protected $casts = [
        'passengers' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (UmrahVoucher $voucher): void {
            if (blank($voucher->voucher_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $voucher->voucher_no = 'UV-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }

            if (blank($voucher->lead_guest) && $voucher->customer) {
                $voucher->lead_guest = $voucher->customer->name;
            }

            if (blank($voucher->phone) && $voucher->customer) {
                $voucher->phone = $voucher->customer->mobile;
            }
        });

        static::saving(function (UmrahVoucher $voucher): void {
            if (blank($voucher->lead_guest) && $voucher->customer) {
                $voucher->lead_guest = $voucher->customer->name;
            }

            if (blank($voucher->phone) && $voucher->customer) {
                $voucher->phone = $voucher->customer->mobile;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function umrahBooking(): BelongsTo
    {
        return $this->belongsTo(UmrahBooking::class);
    }

    public function umrahPackage(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class);
    }

    public function getTravelersTextAttribute(): string
    {
        $parts = [];

        if ($this->adults > 0) {
            $parts[] = $this->adults . ' Adult' . ($this->adults > 1 ? 's' : '');
        }

        if ($this->children > 0) {
            $parts[] = $this->children . ' Child' . ($this->children > 1 ? 'ren' : '');
        }

        if ($this->infants > 0) {
            $parts[] = $this->infants . ' Infant' . ($this->infants > 1 ? 's' : '');
        }

        return count($parts) > 0 ? implode(' - ', $parts) : '-';
    }

    public function getPassengersListAttribute(): array
    {
        return is_array($this->passengers) ? $this->passengers : [];
    }
}