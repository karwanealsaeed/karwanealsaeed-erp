<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Outstanding Balance Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Customers jinke balance amounts pending hain.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Visa Balance</p>
                <h3 class="text-xl font-bold text-red-600">
                    PKR {{ number_format($data['visa_total'], 2) }}
                </h3>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Flight Balance</p>
                <h3 class="text-xl font-bold text-red-600">
                    PKR {{ number_format($data['flight_total'], 2) }}
                </h3>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Umrah Balance</p>
                <h3 class="text-xl font-bold text-red-600">
                    PKR {{ number_format($data['umrah_total'], 2) }}
                </h3>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hotel Balance</p>
                <h3 class="text-xl font-bold text-red-600">
                    PKR {{ number_format($data['hotel_total'], 2) }}
                </h3>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Outstanding</p>
                <h3 class="text-xl font-bold text-red-700">
                    PKR {{ number_format($data['grand_total'], 2) }}
                </h3>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Customer Wise Outstanding Balance
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-6 py-3">Customer</th>
                            <th class="text-left px-6 py-3">Mobile</th>
                            <th class="text-left px-6 py-3">Passport</th>
                            <th class="text-right px-6 py-3">Visa</th>
                            <th class="text-right px-6 py-3">Flight</th>
                            <th class="text-right px-6 py-3">Umrah</th>
                            <th class="text-right px-6 py-3">Hotel</th>
                            <th class="text-right px-6 py-3">Total Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-6 py-3 font-medium">
                                    {{ $row['customer_name'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['mobile'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['passport_no'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['visa_balance'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['flight_balance'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['umrah_balance'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['hotel_balance'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-red-600">
                                    PKR {{ number_format($row['total_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                                    No outstanding balance found.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                            <td class="px-6 py-3" colspan="3">Grand Total</td>

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

                            <td class="px-6 py-3 text-right text-red-700">
                                PKR {{ number_format($data['grand_total'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
