<?php

namespace App\Models;

use App\Services\SupplierBillBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_no',
        'supplier_bill_id',
        'supplier_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_account_head_id',
        'reference_no',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'account_voucher_id',
        'posted_by',
        'posted_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupplierPayment $payment): void {
            $payment->payment_date ??= today();
            $payment->status ??= 'Pending';

            if (
                blank($payment->created_by)
                && auth()->check()
            ) {
                $payment->created_by = auth()->id();
            }
        });

        static::saving(function (SupplierPayment $payment): void {
            $amount = round(
                (float) ($payment->amount ?? 0),
                2
            );

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Payment amount zero se zyada honi chahiye.',
                ]);
            }

            $bill = SupplierBill::query()
                ->find($payment->supplier_bill_id);

            if (! $bill) {
                throw ValidationException::withMessages([
                    'supplier_bill_id' =>
                        'Selected supplier bill nahi mila.',
                ]);
            }

            $payment->supplier_id = $bill->supplier_id;
            $payment->amount = $amount;

            if (
                $payment->exists
                && $payment->getOriginal('status') === 'Approved'
                && $payment->isDirty([
                    'supplier_bill_id',
                    'supplier_id',
                    'amount',
                    'payment_account_head_id',
                ])
            ) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Approved supplier payment change nahi ki ja sakti.',
                ]);
            }
        });

        static::created(function (SupplierPayment $payment): void {
            if (blank($payment->payment_no)) {
                $payment->updateQuietly([
                    'payment_no' => 'SP-' . str_pad(
                        (string) $payment->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        });

        static::saved(function (SupplierPayment $payment): void {
            app(
                SupplierBillBalanceService::class
            )->sync($payment->supplier_bill_id);
        });

        static::deleted(function (SupplierPayment $payment): void {
            app(
                SupplierBillBalanceService::class
            )->sync($payment->supplier_bill_id);
        });
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(
            SupplierBill::class,
            'supplier_bill_id'
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'payment_account_head_id'
        );
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(AccountVoucher::class);
    }
}