<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Validation\ValidationException;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\TravelInsurancePrivateFileService;
use Illuminate\Support\Str;

class TravelInsurancePolicy extends Model
{
    protected $fillable = [
        'policy_no',
        'customer_id',
        'insurance_provider_id',
        'invoice_id',
        'traveller_name',
        'passport_no',
        'date_of_birth',
        'beneficiary_name',
        'beneficiary_relationship',
        'destination_country_id',
        'destination_country',
        'trip_type',
        'coverage_type',
        'coverage_amount',
        'coverage_currency',
        'travel_start_date',
        'travel_end_date',
        'policy_start_date',
        'policy_end_date',
        'premium_amount',
        'supplier_cost',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'profit_amount',
        'currency',
        'status',
        'payment_status',
        'approval_status',
        'approved_by',
        'approved_at',
        'policy_document_name',
        'policy_document_path',
        'policy_document_mime',
        'policy_document_size',
        'notes',
        'expiry_alert_fingerprint',
        'expiry_alert_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' =>
                'date',

            'travel_start_date' =>
                'date',

            'travel_end_date' =>
                'date',

            'policy_start_date' =>
                'date',

            'policy_end_date' =>
                'date',

            'approved_at' =>
                'datetime',

            'coverage_amount' =>
                'decimal:2',

            'premium_amount' =>
                'decimal:2',

            'supplier_cost' =>
                'decimal:2',

            'discount' =>
                'decimal:2',

            'tax' =>
                'decimal:2',

            'total_amount' =>
                'decimal:2',

            'paid_amount' =>
                'decimal:2',

            'balance_amount' =>
                'decimal:2',

            'profit_amount' =>
                'decimal:2',

            'policy_document_size' =>
                'integer',

            'expiry_alert_sent_at' =>
                'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TravelInsurancePolicy $record): void {
            if (filled($record->destination_country_id)) {
                $country = Country::query()
                    ->where('id', $record->destination_country_id)
                    ->where('status', true)
                    ->first();

                if (! $country) {
                    throw ValidationException::withMessages([
                        'destination_country_id' => 'Please select an active destination country.',
                    ]);
                }

                $record->destination_country = $country->name;
            }
        });


        static::creating(
            function (
                TravelInsurancePolicy $policy,
            ): void {
                if (
                    blank(
                        $policy->policy_no,
                    )
                ) {
                    $policy->policy_no =
                        static::generatePolicyNo();
                }
            },
        );

        static::saving(
            function (
                TravelInsurancePolicy $policy,
            ): void {
                $policy->applyFinancialCalculations();
            },
        );

        static::deleting(
            function (
                TravelInsurancePolicy $policy,
            ): void {
                TravelInsurancePrivateFileService::deletePolicyDocument(
                    $policy->policy_document_path,
                );

                $policy->payments()
                    ->whereNotNull(
                        'receipt_path',
                    )
                    ->pluck(
                        'receipt_path',
                    )
                    ->each(
                        function (
                            mixed $path,
                        ): void {
                            TravelInsurancePrivateFileService::deletePaymentReceipt(
                                is_string(
                                    $path,
                                )
                                    ? $path
                                    : null,
                            );
                        },
                    );
            },
        );
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(
            Country::class,
            'destination_country_id',
        );
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class,
        );
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(
            InsuranceProvider::class,
            'insurance_provider_id',
        );
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            Invoice::class,
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by',
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            TravelInsurancePayment::class,
        );
    }

    public function scopeActive(
        Builder $query,
    ): Builder {
        return $query
            ->where(
                'status',
                'Active',
            )
            ->whereDate(
                'policy_end_date',
                '>=',
                today(),
            );
    }

    public function scopeExpired(
        Builder $query,
    ): Builder {
        return $query
            ->whereNotNull(
                'policy_end_date',
            )
            ->whereDate(
                'policy_end_date',
                '<',
                today(),
            )
            ->whereNotIn(
                'status',
                [
                    'Cancelled',
                    'Rejected',
                ],
            );
    }

    public function refreshPaymentTotals(): void
    {
        $approvedPaid =
            (float) $this->payments()
                ->where(
                    'status',
                    'Approved',
                )
                ->sum(
                    'amount',
                );

        $total =
            round(
                (float) $this->total_amount,
                2,
            );

        $paid =
            round(
                max(
                    0,
                    $approvedPaid,
                ),
                2,
            );

        $balance =
            round(
                max(
                    0,
                    $total - $paid,
                ),
                2,
            );

        $paymentStatus =
            match (true) {
                $paid <= 0 =>
                    'Unpaid',

                $paid < $total =>
                    'Partial',

                default =>
                    'Paid',
            };

        $this->forceFill(
            [
                'paid_amount' =>
                    $paid,

                'balance_amount' =>
                    $balance,

                'payment_status' =>
                    $paymentStatus,
            ],
        )->saveQuietly();
    }

    public function getOutstandingAmountAttribute(): float
    {
        return round(
            max(
                0,
                (float) $this->balance_amount,
            ),
            2,
        );
    }

    private function applyFinancialCalculations(): void
    {
        $premium =
            max(
                0,
                (float) $this->premium_amount,
            );

        $supplierCost =
            max(
                0,
                (float) $this->supplier_cost,
            );

        $discount =
            max(
                0,
                (float) $this->discount,
            );

        $tax =
            max(
                0,
                (float) $this->tax,
            );

        $paid =
            max(
                0,
                (float) $this->paid_amount,
            );

        $total =
            round(
                max(
                    0,
                    $premium - $discount + $tax,
                ),
                2,
            );

        $profit =
            round(
                $total - $supplierCost,
                2,
            );

        $balance =
            round(
                max(
                    0,
                    $total - $paid,
                ),
                2,
            );

        $this->paid_amount =
            round(
                $paid,
                2,
            );

        $this->total_amount =
            $total;

        $this->profit_amount =
            $profit;

        $this->balance_amount =
            $balance;

        $this->payment_status =
            match (true) {
                $paid <= 0 =>
                    'Unpaid',

                $paid < $total =>
                    'Partial',

                default =>
                    'Paid',
            };
    }

    private static function generatePolicyNo(): string
    {
        do {
            $policyNo =
                'TI-'
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
                    'policy_no',
                    $policyNo,
                )
                ->exists()
        );

        return $policyNo;
    }
}