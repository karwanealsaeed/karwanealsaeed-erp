<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class UmrahBooking extends Model
{
    protected $guarded = [];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'package_price_per_person' => 'decimal:2',
        'package_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (UmrahBooking $booking): void {
            if (blank($booking->booking_no)) {
                $nextId = ((int) self::max('id')) + 1;
                $booking->booking_no = 'UB-' . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
            }

            self::calculateAmounts($booking);
        });

        static::updating(function (UmrahBooking $booking): void {
            self::calculateAmounts($booking);
        });

        static::created(function (UmrahBooking $booking): void {
            self::reduceSeatsForBooking($booking);
        });

        static::updated(function (UmrahBooking $booking): void {
            if (
                $booking->wasChanged('umrah_package_id')
                || $booking->wasChanged('seats_count')
                || $booking->wasChanged('status')
            ) {
                self::restoreOriginalSeats($booking);
                self::reduceSeatsForBooking($booking);
            }
        });

        static::deleted(function (UmrahBooking $booking): void {
            self::restoreSeats(
                $booking->umrah_package_id ? (int) $booking->umrah_package_id : null,
                (int) ($booking->seats_count ?: 1)
            );
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function umrahPackage(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(UmrahPayment::class);
    }

    public static function calculateAmounts(UmrahBooking $booking): void
    {
        $seats = max((int) ($booking->seats_count ?: 1), 1);
        $pricePerPerson = (float) ($booking->package_price_per_person ?: 0);

        if ($pricePerPerson > 0) {
            $booking->package_amount = $pricePerPerson * $seats;
        }

        $booking->balance_amount = max(
            ((float) ($booking->package_amount ?: 0)) - ((float) ($booking->paid_amount ?: 0)),
            0
        );
    }

    private static function shouldHoldSeats(?string $status): bool
    {
        return ! in_array($status, [
            'Cancelled',
            'Canceled',
            'Refunded',
            'Void',
            'Inactive',
        ], true);
    }

    private static function reduceSeatsForBooking(UmrahBooking $booking): void
    {
        if (! self::shouldHoldSeats((string) $booking->status)) {
            return;
        }

        self::reduceSeats(
            $booking->umrah_package_id ? (int) $booking->umrah_package_id : null,
            (int) ($booking->seats_count ?: 1)
        );
    }

    private static function restoreOriginalSeats(UmrahBooking $booking): void
    {
        $originalPackageId = $booking->getOriginal('umrah_package_id');
        $originalSeats = $booking->getOriginal('seats_count');
        $originalStatus = $booking->getOriginal('status');

        if (! self::shouldHoldSeats((string) $originalStatus)) {
            return;
        }

        self::restoreSeats(
            $originalPackageId ? (int) $originalPackageId : null,
            (int) ($originalSeats ?: 1)
        );
    }

    private static function reduceSeats(?int $packageId, int $seats): void
    {
        if (! $packageId || $seats <= 0) {
            return;
        }

        $package = UmrahPackage::query()
            ->with('umrahGroupFlight')
            ->find($packageId);

        if (! $package || ! $package->umrahGroupFlight) {
            return;
        }

        $groupFlight = $package->umrahGroupFlight;

        DB::table('umrah_group_flights')
            ->where('id', $groupFlight->id)
            ->update([
                'seats_left' => DB::raw('GREATEST(seats_left - ' . $seats . ', 0)'),
            ]);

        self::syncPackageSeats($packageId);
    }

    private static function restoreSeats(?int $packageId, int $seats): void
    {
        if (! $packageId || $seats <= 0) {
            return;
        }

        $package = UmrahPackage::query()
            ->with('umrahGroupFlight')
            ->find($packageId);

        if (! $package || ! $package->umrahGroupFlight) {
            return;
        }

        $groupFlight = $package->umrahGroupFlight;

        DB::table('umrah_group_flights')
            ->where('id', $groupFlight->id)
            ->update([
                'seats_left' => DB::raw('LEAST(seats_left + ' . $seats . ', seats_total)'),
            ]);

        self::syncPackageSeats($packageId);
    }

    private static function syncPackageSeats(int $packageId): void
    {
        $package = UmrahPackage::query()
            ->with('umrahGroupFlight')
            ->find($packageId);

        if (! $package || ! $package->umrahGroupFlight) {
            return;
        }

        DB::table('umrah_packages')
            ->where('id', $package->id)
            ->update([
                'seats_available' => $package->umrahGroupFlight->fresh()?->seats_total ?? 0,
                'seats_left' => $package->umrahGroupFlight->fresh()?->seats_left ?? 0,
            ]);
    }
}