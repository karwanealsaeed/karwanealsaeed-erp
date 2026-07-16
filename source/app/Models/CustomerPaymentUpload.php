<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerPaymentUpload extends Model
{
    protected $fillable = [
        'customer_id',
        'upload_no',
        'service_type',
        'receipt_type',
        'invoice_id',
        'receipt_account_head_id',
        'receivable_account_head_id',
        'advance_account_head_id',
        'reference_no',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_id',
        'receipt_file',
        'status',
        'approved_at',
        'approved_by',
        'account_voucher_id',
        'invoice_balance_applied',
        'posted_by',
        'posted_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'voided_by',
        'voided_at',
        'void_reason',
        'customer_notes',
        'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'voided_at' => 'datetime',
        'invoice_balance_applied' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (
            CustomerPaymentUpload $receipt
        ): void {
            if (blank($receipt->upload_no)) {
                $receipt->upload_no =
                    'CPU-'
                    . now()->format(
                        'YmdHis'
                    )
                    . '-'
                    . Str::upper(
                        Str::random(5)
                    );
            }

            $receipt->status ??= 'Pending';

            $receipt->receipt_type ??=
                'Invoice Payment';
        });

        static::saving(function (
            CustomerPaymentUpload $receipt
        ): void {
            $amount = round(
                (float) ($receipt->amount ?? 0),
                2
            );

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Receipt amount zero se zyada honi chahiye.',
                ]);
            }

            $receipt->amount = $amount;

            if (
                $receipt->receipt_type
                === 'Customer Advance'
            ) {
                $receipt->invoice_id = null;

                $receipt
                    ->receivable_account_head_id =
                    null;

                $receipt
                    ->invoice_balance_applied =
                    false;
            } else {
                $receipt->receipt_type =
                    'Invoice Payment';

                $receipt
                    ->advance_account_head_id =
                    null;
            }

            if (! $receipt->exists) {
                return;
            }

            $originalStatus =
                $receipt->getOriginal('status');

            if ($originalStatus === 'Cancelled') {
                throw ValidationException::withMessages([
                    'status' =>
                        'Cancelled customer receipt edit nahi ho sakti.',
                ]);
            }

            if ($originalStatus !== 'Approved') {
                return;
            }

            if (
                $receipt->isDirty([
                    'customer_id',
                    'amount',
                    'payment_date',
                    'payment_method',
                    'transaction_id',
                    'reference_no',
                ])
            ) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Approved receipt ki core payment details change nahi ki ja sakti.',
                ]);
            }

            /*
             * Legacy Approved receipt jiska voucher missing
             * hai usme invoice/account allocation allow hai.
             */
            if (
                $receipt->getOriginal(
                    'account_voucher_id'
                )
                && $receipt->isDirty([
                    'receipt_type',
                    'invoice_id',
                    'receipt_account_head_id',
                    'receivable_account_head_id',
                    'advance_account_head_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'receipt_account_head_id' =>
                        'Posted receipt ke accounts Edit se change nahi honge. Correct GL action use karein.',
                ]);
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class
        );
    }

    public function receiptAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'receipt_account_head_id'
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'voided_by'
        );
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(
            AccountVoucher::class
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