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
                        Service Wise Sales Report
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
                    Total Records
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_records']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Sales
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    PKR {{ number_format($data['total_sales'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Paid
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['total_paid'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Balance
                </p>

                <h3 class="text-2xl font-bold text-red-600">
                    PKR {{ number_format($data['total_balance'], 2) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Service Wise Sales Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                Service
                            </th>

                            <th class="px-6 py-3 text-right">
                                Records
                            </th>

                            <th class="px-6 py-3 text-right">
                                Total Sales
                            </th>

                            <th class="px-6 py-3 text-right">
                                Paid Amount
                            </th>

                            <th class="px-6 py-3 text-right">
                                Balance Amount
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['service'] }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['count']) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-blue-600">
                                    PKR {{ number_format($row['total'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-green-600">
                                    PKR {{ number_format($row['paid'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-red-600">
                                    PKR {{ number_format($row['balance'], 2) }}
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

                            <td class="px-6 py-3 text-right text-blue-700">
                                PKR {{ number_format($data['total_sales'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-green-700">
                                PKR {{ number_format($data['total_paid'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-red-700">
                                PKR {{ number_format($data['total_balance'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
