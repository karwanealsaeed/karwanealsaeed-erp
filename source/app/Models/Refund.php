<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Refund extends Model
{
    protected $fillable = [
        'refund_no',
        'customer_id',
        'refund_type',
        'invoice_id',
        'customer_payment_upload_id',
        'service_type',
        'service_reference',
        'refund_date',
        'refund_amount',
        'reason',
        'refund_account_head_id',
        'receivable_account_head_id',
        'advance_account_head_id',
        'status',
        'account_voucher_id',
        'invoice_balance_reversed',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'posted_by',
        'posted_at',
        'voided_by',
        'voided_at',
        'void_reason',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'refund_date' => 'date',
        'refund_amount' => 'decimal:2',
        'invoice_balance_reversed' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Refund $refund): void {
            if (blank($refund->refund_no)) {
                $refund->refund_no = 'REF-'
                    . now()->format('YmdHis')
                    . '-'
                    . Str::upper(Str::random(4));
            }

            $refund->refund_date ??= today();
            $refund->status ??= 'Pending';
            $refund->refund_type ??= 'Invoice Refund';

            if (
                blank($refund->created_by)
                && auth()->check()
            ) {
                $refund->created_by = auth()->id();
            }
        });

        static::saving(function (Refund $refund): void {
            $amount = round(
                (float) ($refund->refund_amount ?? 0),
                2
            );

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Refund amount zero se zyada honi chahiye.',
                ]);
            }

            $refund->refund_amount = $amount;

            if ($refund->refund_type === 'Customer Advance Refund') {
                $refund->invoice_id = null;
                $refund->receivable_account_head_id = null;
                $refund->invoice_balance_reversed = false;
            } else {
                $refund->refund_type = 'Invoice Refund';
                $refund->advance_account_head_id = null;
            }

            if (! $refund->exists) {
                return;
            }

            $originalStatus = $refund->getOriginal('status');

            if ($originalStatus === 'Cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Cancelled refund edit nahi ho sakta.',
                ]);
            }

            if ($originalStatus !== 'Approved') {
                return;
            }

            if (
                $refund->isDirty([
                    'customer_id',
                    'refund_amount',
                    'refund_date',
                    'refund_type',
                    'invoice_id',
                    'customer_payment_upload_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Approved refund ki core details change nahi ki ja sakti.',
                ]);
            }

            if (
                $refund->getOriginal('account_voucher_id')
                && $refund->isDirty([
                    'refund_account_head_id',
                    'receivable_account_head_id',
                    'advance_account_head_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'refund_account_head_id' => 'Posted refund ke accounts Edit se change nahi honge. Correct GL action use karein.',
                ]);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function originalReceipt(): BelongsTo
    {
        return $this->belongsTo(
            CustomerPaymentUpload::class,
            'customer_payment_upload_id'
        );
    }

    public function refundAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'refund_account_head_id'
        );
    }

    public function receivableAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'receivable_account_head_id'
        );
    }

    public function advanceAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'advance_account_head_id'
        );
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(AccountVoucher::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'voided_by'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function getPostingStatusAttribute(): string
    {
        if ($this->status === 'Cancelled') {
            return 'Voided';
        }

        if (
            $this->status === 'Approved'
            && $this->account_voucher_id
        ) {
            return 'Posted';
        }

        if ($this->status === 'Approved') {
            return 'Missing GL';
        }

        return 'Not Posted';
    }
}