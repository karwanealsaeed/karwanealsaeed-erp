<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Customer Wise Sales Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Har customer ki Visa, Flight, Umrah aur Hotel sales summary.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Customers
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_customers']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Grand Sales
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    PKR {{ number_format($data['grand_sales'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Grand Paid
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['grand_paid'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Grand Balance
                </p>

                <h3 class="text-2xl font-bold text-red-600">
                    PKR {{ number_format($data['grand_balance'], 2) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Visa Sales
                </p>

                <h3 class="text-xl font-bold text-blue-600">
                    PKR {{ number_format($data['visa_total'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Flight Sales
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    PKR {{ number_format($data['flight_total'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Umrah Sales
                </p>

                <h3 class="text-xl font-bold text-purple-600">
                    PKR {{ number_format($data['umrah_total'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Hotel Sales
                </p>

                <h3 class="text-xl font-bold text-orange-600">
                    PKR {{ number_format($data['hotel_total'], 2) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Customer Wise Sales Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Customer</th>
                            <th class="px-6 py-3 text-left">Mobile</th>
                            <th class="px-6 py-3 text-left">Passport</th>
                            <th class="px-6 py-3 text-right">Visa</th>
                            <th class="px-6 py-3 text-right">Flight</th>
                            <th class="px-6 py-3 text-right">Umrah</th>
                            <th class="px-6 py-3 text-right">Hotel</th>
                            <th class="px-6 py-3 text-right">Total Sales</th>
                            <th class="px-6 py-3 text-right">Paid</th>
                            <th class="px-6 py-3 text-right">Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['customer_name'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['mobile'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['passport_no'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['visa_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['flight_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['umrah_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    PKR {{ number_format($row['hotel_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-blue-600">
                                    PKR {{ number_format($row['total_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-green-600">
                                    PKR {{ number_format($row['total_paid'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-bold text-red-600">
                                    PKR {{ number_format($row['total_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                                    Abhi kisi customer ki sales nahi mili.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3" colspan="3">
                                Grand Total
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

                            <td class="px-6 py-3 text-right text-blue-700">
                                PKR {{ number_format($data['grand_sales'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-green-700">
                                PKR {{ number_format($data['grand_paid'], 2) }}
                            </td>

                            <td class="px-6 py-3 text-right text-red-700">
                                PKR {{ number_format($data['grand_balance'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
