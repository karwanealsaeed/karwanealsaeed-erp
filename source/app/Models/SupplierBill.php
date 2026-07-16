<?php

namespace App\Models;

use App\Services\SupplierBillBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class SupplierBill extends Model
{
    protected $fillable = [
        'bill_no',
        'supplier_id',
        'supplier_invoice_no',
        'bill_date',
        'due_date',
        'service_type',
        'expense_account_head_id',
        'payable_account_head_id',
        'reference_no',
        'description',
        'sub_total',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'approval_status',
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
        'bill_date' => 'date',
        'due_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupplierBill $bill): void {
            $bill->bill_date ??= today();
            $bill->status ??= 'Draft';
            $bill->approval_status ??= 'Pending';

            if (
                blank($bill->created_by)
                && auth()->check()
            ) {
                $bill->created_by = auth()->id();
            }
        });

        static::saving(function (SupplierBill $bill): void {
            $subTotal = max(
                0,
                (float) ($bill->sub_total ?? 0)
            );

            $discount = max(
                0,
                (float) ($bill->discount ?? 0)
            );

            $tax = max(
                0,
                (float) ($bill->tax ?? 0)
            );

            $totalAmount = round(
                max(
                    0,
                    $subTotal - $discount + $tax
                ),
                2
            );

            $approvedPayments = $bill->exists
                ? round(
                    (float) $bill->payments()
                        ->where('status', 'Approved')
                        ->sum('amount'),
                    2
                )
                : 0;

            if ($approvedPayments > $totalAmount) {
                throw ValidationException::withMessages([
                    'total_amount' =>
                        'Bill total approved payments se kam nahi ho sakta.',
                ]);
            }

            if (
                $bill->approval_status === 'Cancelled'
                && $approvedPayments > 0
            ) {
                throw ValidationException::withMessages([
                    'status' =>
                        'Payment wale supplier bill ko cancel nahi kiya ja sakta.',
                ]);
            }

            $bill->sub_total = $subTotal;
            $bill->discount = $discount;
            $bill->tax = $tax;
            $bill->total_amount = $totalAmount;
            $bill->paid_amount = $approvedPayments;

            $bill->balance_amount = round(
                max(
                    0,
                    $totalAmount - $approvedPayments
                ),
                2
            );

            if ($bill->approval_status === 'Cancelled') {
                $bill->status = 'Cancelled';
            } elseif ($bill->approval_status !== 'Approved') {
                $bill->status = 'Draft';
            } else {
                $bill->status = match (true) {
                    $totalAmount > 0
                        && $bill->balance_amount <= 0 =>
                            'Paid',

                    $approvedPayments > 0 =>
                        'Partially Paid',

                    default => 'Unpaid',
                };
            }
        });

        static::created(function (SupplierBill $bill): void {
            if (blank($bill->bill_no)) {
                $bill->updateQuietly([
                    'bill_no' => 'SB-' . str_pad(
                        (string) $bill->id,
                        6,
                        '0',
                        STR_PAD_LEFT
                    ),
                ]);
            }
        });

        static::saved(function (SupplierBill $bill): void {
            app(
                SupplierBillBalanceService::class
            )->sync($bill);
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function expenseAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'expense_account_head_id'
        );
    }

    public function payableAccountHead(): BelongsTo
    {
        return $this->belongsTo(
            AccountHead::class,
            'payable_account_head_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
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

    public function getDaysOverdueAttribute(): int
    {
        if (
            (float) $this->balance_amount <= 0
            || in_array(
                $this->status,
                ['Draft', 'Paid', 'Cancelled'],
                true
            )
        ) {
            return 0;
        }

        $dueDate = $this->due_date
            ?: $this->bill_date;

        if (! $dueDate || $dueDate->gte(today())) {
            return 0;
        }

        return (int) $dueDate->diffInDays(today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->days_overdue > 0;
    }
}