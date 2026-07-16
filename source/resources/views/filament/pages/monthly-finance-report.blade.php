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
                        Monthly Finance Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Month: {{ $data['formatted_month'] }} | {{ $data['start_date'] }} to {{ $data['end_date'] }}
                    </p>
                </div>

                <div class="w-full md:w-64">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Month
                    </label>

                    <input
                        type="month"
                        wire:model.live="reportMonth"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Collection
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['total_collection'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Expenses
                </p>

                <h3 class="text-2xl font-bold text-red-600">
                    PKR {{ number_format($data['total_expenses'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Refunds
                </p>

                <h3 class="text-2xl font-bold text-orange-600">
                    PKR {{ number_format($data['total_refunds'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Net Profit
                </p>

                <h3 class="text-2xl font-bold {{ $data['net_profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    PKR {{ number_format($data['net_profit'], 2) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Collection Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Service</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['service'] }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-green-600">
                                    PKR {{ number_format($row['amount'], 2) }}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3">
                                Total Collection
                            </td>

                            <td class="px-6 py-3 text-right text-green-700">
                                PKR {{ number_format($data['total_collection'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Profit Summary
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-3 font-semibold">Total Collection</td>
                            <td class="px-6 py-3 text-right font-bold text-green-600">
                                PKR {{ number_format($data['total_collection'], 2) }}
                            </td>
                        </tr>

                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-3 font-semibold">Total Expenses</td>
                            <td class="px-6 py-3 text-right font-bold text-red-600">
                                PKR {{ number_format($data['total_expenses'], 2) }}
                            </td>
                        </tr>

                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-3 font-semibold">Total Refunds</td>
                            <td class="px-6 py-3 text-right font-bold text-orange-600">
                                PKR {{ number_format($data['total_refunds'], 2) }}
                            </td>
                        </tr>

                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-3 font-semibold">Total Cash Out</td>
                            <td class="px-6 py-3 text-right font-bold text-red-700">
                                PKR {{ number_format($data['total_cash_out'], 2) }}
                            </td>
                        </tr>

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3">Net Profit</td>
                            <td class="px-6 py-3 text-right {{ $data['net_profit'] >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                                PKR {{ number_format($data['net_profit'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>