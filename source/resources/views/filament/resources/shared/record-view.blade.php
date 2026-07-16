<x-filament-panels::page>
    @php
        $attributes = $record->getAttributes();

        $hiddenFields = [
            'id',
            'password',
            'remember_token',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $formatLabel = fn ($field) => str($field)->replace('_', ' ')->title()->toString();

        $formatValue = function ($value): string {
            if ($value instanceof \Carbon\Carbon) {
                return $value->format('d M Y');
            }

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            if ($value === null || $value === '') {
                return '-';
            }

            return (string) $value;
        };

        $titleFields = [
            'application_no',
            'booking_no',
            'invoice_no',
            'refund_no',
            'expense_no',
            'name',
            'company_name',
            'email',
        ];

        $recordTitle = 'Record #' . $record->getKey();

        foreach ($titleFields as $field) {
            if (! blank($record->{$field} ?? null)) {
                $recordTitle = $record->{$field};
                break;
            }
        }

        $customerName = $record->customer?->name ?? null;
        $countryName = $record->country?->name ?? null;
        $visaTypeName = $record->visaType?->name ?? null;
        $airlineName = $record->airline?->name ?? null;
        $fromAirport = $record->fromAirport?->iata_code ?? $record->fromAirport?->name ?? null;
        $toAirport = $record->toAirport?->iata_code ?? $record->toAirport?->name ?? null;
        $categoryName = $record->category?->name ?? null;
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $recordTitle }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Complete record details
                    </p>
                </div>

                @if (! blank($record->status ?? null))
                    <div>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                            {{ $record->status }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        @if ($customerName || $countryName || $visaTypeName || $airlineName || $fromAirport || $toAirport || $categoryName)
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                        Related Information
                    </h3>

                    <div class="space-y-3 text-sm">
                        @if ($customerName)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Customer</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $customerName }}</span>
                            </div>
                        @endif

                        @if ($countryName)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Country</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $countryName }}</span>
                            </div>
                        @endif

                        @if ($visaTypeName)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Visa Type</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $visaTypeName }}</span>
                            </div>
                        @endif

                        @if ($airlineName)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Airline</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $airlineName }}</span>
                            </div>
                        @endif

                        @if ($fromAirport)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">From Airport</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $fromAirport }}</span>
                            </div>
                        @endif

                        @if ($toAirport)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">To Airport</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $toAirport }}</span>
                            </div>
                        @endif

                        @if ($categoryName)
                            <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                                <span class="font-semibold text-gray-600 dark:text-gray-400">Category</span>
                                <span class="text-right text-gray-900 dark:text-white">{{ $categoryName }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                    <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                        Record Summary
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Record ID</span>
                            <span class="text-right text-gray-900 dark:text-white">{{ $record->getKey() }}</span>
                        </div>

                        <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Created At</span>
                            <span class="text-right text-gray-900 dark:text-white">
                                {{ $record->created_at?->format('d M Y h:i A') ?? '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4 border-b pb-2 dark:border-gray-800">
                            <span class="font-semibold text-gray-600 dark:text-gray-400">Updated At</span>
                            <span class="text-right text-gray-900 dark:text-white">
                                {{ $record->updated_at?->format('d M Y h:i A') ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Detail Information
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($attributes as $field => $value)
                            @continue(in_array($field, $hiddenFields, true))

                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="w-64 px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $formatLabel($field) }}
                                </td>

                                <td class="px-6 py-3 text-gray-900 dark:text-white">
                                    {{ $formatValue($value) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="w-64 px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                Created At
                            </td>

                            <td class="px-6 py-3 text-gray-900 dark:text-white">
                                {{ $record->created_at?->format('d M Y h:i A') ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="w-64 px-6 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                Updated At
                            </td>

                            <td class="px-6 py-3 text-gray-900 dark:text-white">
                                {{ $record->updated_at?->format('d M Y h:i A') ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>