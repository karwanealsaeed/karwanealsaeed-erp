<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Support\Str;

class CustomerPassportService
{
    public function defaultsForCustomer(
        ?int $customerId,
    ): array {
        if (! $customerId) {
            return $this->emptyDefaults();
        }

        $customer = Customer::query()
            ->find($customerId);

        if (! $customer) {
            return $this->emptyDefaults();
        }

        $passportDocuments =
            CustomerDocument::query()
                ->where(
                    'customer_id',
                    $customer->id,
                )
                ->where(
                    function ($query): void {
                        $query
                            ->whereNull('status')
                            ->orWhere(
                                'status',
                                '!=',
                                'Cancelled',
                            );
                    },
                )
                ->latest('id')
                ->get()
                ->filter(
                    fn (
                        CustomerDocument $document,
                    ): bool =>
                        $this->isPassportDocument(
                            $document,
                        ),
                )
                ->values();

        $passportDocument =
            $passportDocuments->first(
                fn (
                    CustomerDocument $document,
                ): bool =>
                    filled(
                        $document->expiry_date,
                    ),
            )
            ?? $passportDocuments->first();

        return [
            'passport_no' =>
                $customer->passport_no,

            'passport_expiry_date' =>
                $passportDocument
                    ?->expiry_date
                    ?->toDateString(),

            'document_id' =>
                $passportDocument?->id,
        ];
    }

    public function isPassportDocument(
        CustomerDocument $document,
    ): bool {
        $types = [];

        if (
            is_array(
                $document->document_types,
            )
        ) {
            $types = array_merge(
                $types,
                $document->document_types,
            );
        }

        if ($document->document_type) {
            $types = array_merge(
                $types,
                explode(
                    ',',
                    $document->document_type,
                ),
            );
        }

        return collect($types)->contains(
            fn (mixed $type): bool =>
                Str::lower(
                    trim((string) $type),
                ) === 'passport',
        );
    }

    private function emptyDefaults(): array
    {
        return [
            'passport_no' => null,
            'passport_expiry_date' => null,
            'document_id' => null,
        ];
    }
}
