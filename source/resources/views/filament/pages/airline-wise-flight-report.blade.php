<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Airline Wise Flight Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Airline wise flight bookings, sales, paid aur balance summary.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Airlines
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_airlines']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Bookings
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    {{ number_format($data['total_bookings']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Flight Sales
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['total_sales'], 2) }}
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

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Draft
                </p>

                <h3 class="text-xl font-bold text-gray-600">
                    {{ number_format($data['total_draft']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Booked
                </p>

                <h3 class="text-xl font-bold text-blue-600">
                    {{ number_format($data['total_booked']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ticket Issued
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    {{ number_format($data['total_ticket_issued']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cancelled
                </p>

                <h3 class="text-xl font-bold text-red-600">
                    {{ number_format($data['total_cancelled']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Refunded
                </p>

                <h3 class="text-xl font-bold text-orange-600">
                    {{ number_format($data['total_refunded']) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Airline Wise Flight Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Airline</th>
                            <th class="px-6 py-3 text-right">Bookings</th>
                            <th class="px-6 py-3 text-right">Sales</th>
                            <th class="px-6 py-3 text-right">Paid</th>
                            <th class="px-6 py-3 text-right">Balance</th>
                            <th class="px-6 py-3 text-right">Draft</th>
                            <th class="px-6 py-3 text-right">Booked</th>
                            <th class="px-6 py-3 text-right">Ticket Issued</th>
                            <th class="px-6 py-3 text-right">Cancelled</th>
                            <th class="px-6 py-3 text-right">Refunded</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['airline_name'] }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['total_bookings']) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-blue-600">
                                    PKR {{ number_format($row['total_sales'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-green-600">
                                    PKR {{ number_format($row['paid_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-red-600">
                                    PKR {{ number_format($row['balance_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['draft']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['booked']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['ticket_issued']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['cancelled']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['refunded']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                                    Abhi airline wise flight data nahi mila.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3">
                                Grand Total
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_bookings']) }}
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

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_draft']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_booked']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_ticket_issued']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_cancelled']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_refunded']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
