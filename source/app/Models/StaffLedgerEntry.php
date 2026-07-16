<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class StaffLedgerEntry extends Model
{
    protected $fillable = [
        'entry_no',
        'staff_id',
        'entry_date',
        'category',
        'reference_no',
        'reference_type',
        'reference_id',
        'debit',
        'credit',
        'description',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'reference_id' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (StaffLedgerEntry $entry): void {
            $entry->entry_date ??= today();
            $entry->status ??= 'Pending';

            if (blank($entry->created_by) && auth()->check()) {
                $entry->created_by = auth()->id();
            }
        });

        static::saving(function (StaffLedgerEntry $entry): void {
            $entry->validateAmounts();
        });

        static::created(function (StaffLedgerEntry $entry): void {
            if (blank($entry->entry_no)) {
                $entry->updateQuietly([
                    'entry_no' => 'SLE-' . str_pad(
                        (string) $entry->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        });
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'Approved');
    }

    public function getEntryTypeAttribute(): string
    {
        return (float) $this->debit > 0
            ? 'Debit'
            : 'Credit';
    }

    public function getAmountAttribute(): float
    {
        return max(
            (float) $this->debit,
            (float) $this->credit
        );
    }

    public function getBalanceEffectAttribute(): float
    {
        return (float) $this->debit - (float) $this->credit;
    }

    private function validateAmounts(): void
    {
        $debit = round((float) ($this->debit ?? 0), 2);
        $credit = round((float) ($this->credit ?? 0), 2);

        if ($debit < 0 || $credit < 0) {
            throw ValidationException::withMessages([
                'debit' => 'Debit aur Credit negative nahi ho sakte.',
            ]);
        }

        if ($debit <= 0 && $credit <= 0) {
            throw ValidationException::withMessages([
                'debit' => 'Debit ya Credit me se ek amount enter karein.',
            ]);
        }

        if ($debit > 0 && $credit > 0) {
            throw ValidationException::withMessages([
                'credit' => 'Ek entry me Debit aur Credit dono enter nahi ho sakte.',
            ]);
        }
    }
}