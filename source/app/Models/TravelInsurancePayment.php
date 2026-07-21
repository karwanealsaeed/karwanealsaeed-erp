<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\TravelInsurancePrivateFileService;
use Illuminate\Support\Str;

class TravelInsurancePayment extends Model
{
    protected $fillable = [
        'payment_no',
        'travel_insurance_policy_id',
        'account_voucher_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'status',
        'posting_status',
        'approved_by',
        'approved_at',
        'receipt_name',
        'receipt_path',
        'receipt_mime',
        'receipt_size',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' =>
                'date',

            'amount' =>
                'decimal:2',

            'approved_at' =>
                'datetime',

            'receipt_size' =>
                'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(
            function (
                TravelInsurancePayment $payment,
            ): void {
                if (
                    blank(
                        $payment->payment_no,
                    )
                ) {
                    $payment->payment_no =
                        static::generatePaymentNo();
                }
            },
        );

        static::saved(
            function (
                TravelInsurancePayment $payment,
            ): void {
                $originalPolicyId =
                    (int) $payment->getOriginal(
                        'travel_insurance_policy_id',
                    );

                $payment->refreshPolicyPaymentTotals();

                if (
                    $originalPolicyId > 0
                    && $originalPolicyId !==
                        (int) $payment->travel_insurance_policy_id
                ) {
                    static::refreshPolicyPaymentTotalsFor(
                        $originalPolicyId,
                    );
                }
            },
        );

        static::deleting(
            function (
                TravelInsurancePayment $payment,
            ): void {
                TravelInsurancePrivateFileService::deletePaymentReceipt(
                    $payment->receipt_path,
                );
            },
        );

        static::deleted(
            function (
                TravelInsurancePayment $payment,
            ): void {
                $payment->refreshPolicyPaymentTotals();
            },
        );
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(
            TravelInsurancePolicy::class,
            'travel_insurance_policy_id',
        );
    }

    public function accountVoucher(): BelongsTo
    {
        return $this->belongsTo(
            AccountVoucher::class,
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by',
        );
    }

    public function isAccountingLocked(): bool
    {
        if (
            $this->posting_status === 'Posted'
        ) {
            return true;
        }

        return filled(
            $this->account_voucher_id,
        );
    }

    private function refreshPolicyPaymentTotals(): void
    {
        static::refreshPolicyPaymentTotalsFor(
            (int) $this->travel_insurance_policy_id,
        );
    }

    private static function refreshPolicyPaymentTotalsFor(
        int $policyId,
    ): void {
        if ($policyId <= 0) {
            return;
        }

        $policy =
            TravelInsurancePolicy::query()
                ->find(
                    $policyId,
                );

        $policy?->refreshPaymentTotals();
    }

    private static function generatePaymentNo(): string
    {
        do {
            $paymentNo =
                'TIP-'
                .now()->format(
                    'Ymd',
                )
                .'-'
                .Str::upper(
                    Str::random(
                        6,
                    ),
                );
        } while (
            static::query()
                ->where(
                    'payment_no',
                    $paymentNo,
                )
                ->exists()
        );

        return $paymentNo;
    }
}