<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Umrah Package Wise Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Umrah package wise bookings, persons, sales, paid aur balance summary.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Packages
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_packages']) }}
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
                    Total Persons
                </p>

                <h3 class="text-2xl font-bold text-purple-600">
                    {{ number_format($data['total_persons']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Umrah Sales
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

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">
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
                    Processing
                </p>

                <h3 class="text-xl font-bold text-yellow-600">
                    {{ number_format($data['total_processing']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Confirmed
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    {{ number_format($data['total_confirmed']) }}
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
                    Completed
                </p>

                <h3 class="text-xl font-bold text-purple-600">
                    {{ number_format($data['total_completed']) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Package Wise Umrah Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Package</th>
                            <th class="px-6 py-3 text-right">Bookings</th>
                            <th class="px-6 py-3 text-right">Persons</th>
                            <th class="px-6 py-3 text-right">Sales</th>
                            <th class="px-6 py-3 text-right">Paid</th>
                            <th class="px-6 py-3 text-right">Balance</th>
                            <th class="px-6 py-3 text-right">Booked</th>
                            <th class="px-6 py-3 text-right">Processing</th>
                            <th class="px-6 py-3 text-right">Confirmed</th>
                            <th class="px-6 py-3 text-right">Cancelled</th>
                            <th class="px-6 py-3 text-right">Completed</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['package_name'] }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['total_bookings']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['total_persons']) }}
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
                                    {{ number_format($row['booked']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['processing']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['confirmed']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['cancelled']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['completed']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-6 text-center text-gray-500">
                                    Abhi umrah package wise data nahi mila.
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

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_persons']) }}
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
                                {{ number_format($data['total_booked']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_processing']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_confirmed']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_cancelled']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_completed']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
