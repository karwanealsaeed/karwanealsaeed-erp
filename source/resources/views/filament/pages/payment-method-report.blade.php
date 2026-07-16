<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Payment Method Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        From
                        {{ \Carbon\Carbon::parse($data['from_date'])->format('d M Y') }}
                        to
                        {{ \Carbon\Carbon::parse($data['to_date'])->format('d M Y') }}
                    </p>
                </div>

                <div class="flex flex-col gap-4 md:flex-row">
                    <div class="w-full md:w-48">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Date
                        </label>

                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <div class="w-full md:w-48">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            To Date
                        </label>

                        <input
                            type="date"
                            wire:model.live="toDate"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Payment Records
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_records']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Visa Collection
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    PKR {{ number_format($data['visa_total'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Flight Collection
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['flight_total'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Grand Total Collection
                </p>

                <h3 class="text-2xl font-bold text-green-700">
                    PKR {{ number_format($data['grand_total'], 2) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Payment Method Wise Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Method</th>
                            <th class="px-6 py-3 text-right">Records</th>
                            <th class="px-6 py-3 text-right">Visa</th>
                            <th class="px-6 py-3 text-right">Flight</th>
                            <th class="px-6 py-3 text-right">Umrah</th>
                            <th class="px-6 py-3 text-right">Hotel</th>
                            <th class="px-6 py-3 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['method'] }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['total_count']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['visa_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['flight_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['umrah_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['hotel_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-green-600">
                                    PKR {{ number_format($row['total_amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3">
                                Grand Total
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_records']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                PKR {{ number_format($data['visa_total'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                PKR {{ number_format($data['flight_total'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                PKR {{ number_format($data['umrah_total'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                PKR {{ number_format($data['hotel_total'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-green-700">
                                PKR {{ number_format($data['grand_total'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
