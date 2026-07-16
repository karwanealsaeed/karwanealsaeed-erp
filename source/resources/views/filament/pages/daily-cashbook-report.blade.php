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
                        Daily Cashbook Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selected date ke payments, expenses, refunds aur net cash summary.
                    </p>
                </div>

                <div class="w-full md:w-64">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Date
                    </label>

                    <input
                        type="date"
                        wire:model.live="reportDate"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Report Date
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $data['formatted_date'] }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Cash In
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['total_cash_in'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Cash Out
                </p>

                <h3 class="text-2xl font-bold text-red-600">
                    PKR {{ number_format($data['total_cash_out'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Net Cash
                </p>

                <h3 class="text-2xl font-bold {{ $data['net_cash'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    PKR {{ number_format($data['net_cash'], 2) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Visa Payments
                </p>

                <h3 class="text-xl font-bold text-blue-600">
                    PKR {{ number_format($data['visa_payments'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Flight Payments
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    PKR {{ number_format($data['flight_payments'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Umrah Payments
                </p>

                <h3 class="text-xl font-bold text-purple-600">
                    PKR {{ number_format($data['umrah_payments'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Hotel Payments
                </p>

                <h3 class="text-xl font-bold text-yellow-600">
                    PKR {{ number_format($data['hotel_payments'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Expenses
                </p>

                <h3 class="text-xl font-bold text-red-600">
                    PKR {{ number_format($data['expenses'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Refunds
                </p>

                <h3 class="text-xl font-bold text-orange-600">
                    PKR {{ number_format($data['refunds'], 2) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <div class="flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Transaction Details
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total Transactions: {{ number_format($data['transaction_count']) }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Type</th>
                            <th class="px-6 py-3 text-left">Service</th>
                            <th class="px-6 py-3 text-left">Reference</th>
                            <th class="px-6 py-3 text-left">Customer</th>
                            <th class="px-6 py-3 text-left">Method</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-right">Cash In</th>
                            <th class="px-6 py-3 text-right">Cash Out</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['transactions'] as $transaction)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold">
                                    <span class="{{ $transaction['type'] === 'Cash In' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction['type'] }}
                                    </span>
                                </td>

                                <td class="px-6 py-3">
                                    {{ $transaction['service'] }}
                                </td>

                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $transaction['reference'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $transaction['customer'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $transaction['method'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $transaction['description'] }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-green-600">
                                    @if ($transaction['cash_in'] > 0)
                                        PKR {{ number_format($transaction['cash_in'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-red-600">
                                    @if ($transaction['cash_out'] > 0)
                                        PKR {{ number_format($transaction['cash_out'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                                    Is date par koi transaction nahi mila.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3" colspan="6">
                                Grand Total
                            </td>

                            <td class="px-6 py-3 text-right text-green-700">
                                PKR {{ number_format($data['total_cash_in'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-red-700">
                                PKR {{ number_format($data['total_cash_out'], 2) }}
                            </td>
                        </tr>

                        <tr class="bg-gray-100 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3" colspan="6">
                                Net Cash
                            </td>

                            <td class="px-6 py-3 text-right {{ $data['net_cash'] >= 0 ? 'text-blue-700' : 'text-red-700' }}" colspan="2">
                                PKR {{ number_format($data['net_cash'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>