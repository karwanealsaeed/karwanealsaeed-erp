<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\TravelInsurancePolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TravelInsuranceInvoiceService
{
    public const SERVICE_TYPE =
        'Travel Insurance';

    public function createOrSync(
        TravelInsurancePolicy $policy,
    ): Invoice {
        return DB::transaction(
            function () use (
                $policy,
            ): Invoice {
                $lockedPolicy =
                    TravelInsurancePolicy::query()
                        ->with(
                            [
                                'customer',
                                'invoice',
                            ],
                        )
                        ->lockForUpdate()
                        ->findOrFail(
                            $policy->getKey(),
                        );

                $this->validatePolicy(
                    $lockedPolicy,
                );

                $invoice =
                    $this->resolveInvoice(
                        $lockedPolicy,
                    );

                if (! $invoice) {
                    $invoice =
                        new Invoice(
                            [
                                'invoice_no' =>
                                    $this->nextInvoiceNo(),

                                'invoice_date' =>
                                    today(),
                            ],
                        );
                }

                $this->assertInvoiceBelongsToPolicy(
                    $invoice,
                    $lockedPolicy,
                );

                $invoice->fill(
                    $this->invoiceData(
                        $lockedPolicy,
                        $invoice,
                    ),
                );

                $invoice->save();

                if (
                    (int) $lockedPolicy->invoice_id
                    !== (int) $invoice->getKey()
                ) {
                    $lockedPolicy->forceFill(
                        [
                            'invoice_id' =>
                                $invoice->getKey(),
                        ],
                    )->saveQuietly();
                }

                return $invoice->fresh(
                    'customer',
                );
            },
        );
    }

    public function syncExisting(
        TravelInsurancePolicy $policy,
    ): ?Invoice {
        $policy->refresh();

        $hasLinkedInvoice =
            filled(
                $policy->invoice_id,
            );

        $hasMatchingInvoice =
            Invoice::query()
                ->where(
                    'service_type',
                    self::SERVICE_TYPE,
                )
                ->where(
                    'service_reference',
                    $policy->policy_no,
                )
                ->exists();

        if (! $hasLinkedInvoice) {
            if (! $hasMatchingInvoice) {
                return null;
            }
        }

        return $this->createOrSync(
            $policy,
        );
    }

    private function resolveInvoice(
        TravelInsurancePolicy $policy,
    ): ?Invoice {
        if (filled($policy->invoice_id)) {
            $linkedInvoice =
                Invoice::query()
                    ->lockForUpdate()
                    ->find(
                        $policy->invoice_id,
                    );

            if ($linkedInvoice) {
                return $linkedInvoice;
            }
        }

        $matches =
            Invoice::query()
                ->where(
                    'service_type',
                    self::SERVICE_TYPE,
                )
                ->where(
                    'service_reference',
                    $policy->policy_no,
                )
                ->lockForUpdate()
                ->get();

        if ($matches->count() > 1) {
            throw ValidationException::withMessages(
                [
                    'invoice_id' =>
                        'Is insurance policy ke liye duplicate invoices mojood hain.',
                ],
            );
        }

        return $matches->first();
    }

    private function invoiceData(
        TravelInsurancePolicy $policy,
        Invoice $invoice,
    ): array {
        $notes =
            $invoice->exists
                ? $invoice->notes
                : $this->defaultNotes(
                    $policy,
                );

        return [
            'customer_id' =>
                $policy->customer_id,

            'invoice_date' =>
                $invoice->invoice_date
                ?: today(),

            'due_date' =>
                $policy->policy_start_date
                ?: $policy->travel_start_date
                ?: today(),

            'service_type' =>
                self::SERVICE_TYPE,

            'service_reference' =>
                $policy->policy_no,

            'sub_total' =>
                $this->money(
                    $policy->premium_amount,
                ),

            'discount' =>
                $this->money(
                    $policy->discount,
                ),

            'tax' =>
                $this->money(
                    $policy->tax,
                ),

            'total_amount' =>
                $this->money(
                    $policy->total_amount,
                ),

            'paid_amount' =>
                $this->money(
                    $policy->paid_amount,
                ),

            'balance_amount' =>
                $this->money(
                    $policy->balance_amount,
                ),

            'status' =>
                $this->invoiceStatus(
                    $policy,
                ),

            'notes' =>
                $notes,
        ];
    }

    private function invoiceStatus(
        TravelInsurancePolicy $policy,
    ): string {
        if (
            in_array(
                $policy->status,
                [
                    'Cancelled',
                    'Rejected',
                ],
                true,
            )
        ) {
            return 'Cancelled';
        }

        if (
            $policy->status === 'Draft'
        ) {
            return 'Draft';
        }

        if (
            $policy->approval_status
            !== 'Approved'
        ) {
            return 'Draft';
        }

        $total =
            $this->money(
                $policy->total_amount,
            );

        $paid =
            $this->money(
                $policy->paid_amount,
            );

        $balance =
            $this->money(
                $policy->balance_amount,
            );

        if ($total <= 0) {
            return 'Draft';
        }

        if ($balance <= 0) {
            return 'Paid';
        }

        if ($paid > 0) {
            return 'Partially Paid';
        }

        return 'Unpaid';
    }

    private function assertInvoiceBelongsToPolicy(
        Invoice $invoice,
        TravelInsurancePolicy $policy,
    ): void {
        if (! $invoice->exists) {
            return;
        }

        $serviceType =
            trim(
                (string) $invoice->service_type,
            );

        $serviceReference =
            trim(
                (string) $invoice->service_reference,
            );

        if (
            $serviceType !==
            self::SERVICE_TYPE
        ) {
            throw ValidationException::withMessages(
                [
                    'invoice_id' =>
                        'Linked invoice Travel Insurance service ki nahi hai.',
                ],
            );
        }

        if (
            $serviceReference !==
            $policy->policy_no
        ) {
            throw ValidationException::withMessages(
                [
                    'invoice_id' =>
                        'Linked invoice kisi doosri insurance policy se related hai.',
                ],
            );
        }

        if (
            (int) $invoice->customer_id
            !== (int) $policy->customer_id
        ) {
            throw ValidationException::withMessages(
                [
                    'invoice_id' =>
                        'Linked invoice ka customer insurance policy customer se match nahi karta.',
                ],
            );
        }
    }

    private function validatePolicy(
        TravelInsurancePolicy $policy,
    ): void {
        if (blank($policy->policy_no)) {
            throw ValidationException::withMessages(
                [
                    'policy_no' =>
                        'Invoice create karne ke liye policy number required hai.',
                ],
            );
        }

        if (blank($policy->customer_id)) {
            throw ValidationException::withMessages(
                [
                    'customer_id' =>
                        'Invoice create karne ke liye customer required hai.',
                ],
            );
        }
    }

    private function nextInvoiceNo(): string
    {
        $lastId =
            Invoice::query()
                ->orderByDesc(
                    'id',
                )
                ->lockForUpdate()
                ->value(
                    'id',
                );

        $next =
            ((int) $lastId)
            + 1;

        do {
            $invoiceNo =
                'INV-'
                .str_pad(
                    (string) $next,
                    5,
                    '0',
                    STR_PAD_LEFT,
                );

            $exists =
                Invoice::query()
                    ->where(
                        'invoice_no',
                        $invoiceNo,
                    )
                    ->exists();

            $next++;
        } while ($exists);

        return $invoiceNo;
    }

    private function defaultNotes(
        TravelInsurancePolicy $policy,
    ): string {
        $parts = [
            'Auto-generated from Travel Insurance Policy '
                .$policy->policy_no
                .'.',
        ];

        if (filled($policy->traveller_name)) {
            $parts[] =
                'Traveller: '
                .$policy->traveller_name
                .'.';
        }

        if (
            filled(
                $policy->destination_country,
            )
        ) {
            $parts[] =
                'Destination: '
                .$policy->destination_country
                .'.';
        }

        return implode(
            ' ',
            $parts,
        );
    }

    private function money(
        mixed $value,
    ): float {
        return round(
            (float) $value,
            2,
        );
    }
}