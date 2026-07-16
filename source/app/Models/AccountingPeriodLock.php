<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class AccountingPeriodLock extends Model
{
    protected $fillable = [
        'name',
        'period_start',
        'period_end',
        'lock_scope',
        'status',
        'created_by',
        'locked_by',
        'locked_at',
        'unlocked_by',
        'unlocked_at',
        'unlock_reason',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(
            function (
                AccountingPeriodLock $period
            ): void {
                $period->status ??= 'Open';
                $period->lock_scope ??=
                    'All Accounting';

                if (
                    blank($period->created_by)
                    && auth()->check()
                ) {
                    $period->created_by =
                        auth()->id();
                }
            }
        );

        static::saving(
            function (
                AccountingPeriodLock $period
            ): void {
                if (
                    $period->period_start
                    && $period->period_end
                    && $period->period_end
                        ->lessThan(
                            $period->period_start
                        )
                ) {
                    throw ValidationException::withMessages([
                        'period_end' =>
                            'Period End, Period Start se pehle nahi ho sakti.',
                    ]);
                }

                if (! $period->exists) {
                    return;
                }

                if (
                    $period->getOriginal('status')
                    !== 'Locked'
                ) {
                    return;
                }

                if (
                    $period->isDirty([
                        'name',
                        'period_start',
                        'period_end',
                        'lock_scope',
                    ])
                ) {
                    throw ValidationException::withMessages([
                        'period_start' =>
                            'Locked accounting period edit nahi ho sakta. Pehle period unlock karein.',
                    ]);
                }
            }
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'locked_by'
        );
    }

    public function unlocker(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'unlocked_by'
        );
    }

    public function getPeriodLabelAttribute(): string
    {
        if (
            ! $this->period_start
            || ! $this->period_end
        ) {
            return '-';
        }

        return $this->period_start
            ->format('d M Y')
            . ' to '
            . $this->period_end
                ->format('d M Y');
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'Locked';
    }
}