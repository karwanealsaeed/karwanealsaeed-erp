<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Visa Type Wise Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Visa type wise applications, sales, paid aur balance summary.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Visa Types
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_visa_types']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Applications
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    {{ number_format($data['total_applications']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Visa Sales
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
                    Applied
                </p>

                <h3 class="text-xl font-bold text-blue-600">
                    {{ number_format($data['total_applied']) }}
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
                    Approved
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    {{ number_format($data['total_approved']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Rejected
                </p>

                <h3 class="text-xl font-bold text-red-600">
                    {{ number_format($data['total_rejected']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Delivered
                </p>

                <h3 class="text-xl font-bold text-purple-600">
                    {{ number_format($data['total_delivered']) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Visa Type Wise Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Visa Type</th>
                            <th class="px-6 py-3 text-right">Applications</th>
                            <th class="px-6 py-3 text-right">Sales</th>
                            <th class="px-6 py-3 text-right">Paid</th>
                            <th class="px-6 py-3 text-right">Balance</th>
                            <th class="px-6 py-3 text-right">Draft</th>
                            <th class="px-6 py-3 text-right">Applied</th>
                            <th class="px-6 py-3 text-right">Processing</th>
                            <th class="px-6 py-3 text-right">Approved</th>
                            <th class="px-6 py-3 text-right">Rejected</th>
                            <th class="px-6 py-3 text-right">Delivered</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['visa_type_name'] }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['total_applications']) }}
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
                                    {{ number_format($row['applied']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['processing']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['approved']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['rejected']) }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['delivered']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-6 text-center text-gray-500">
                                    Abhi visa type wise data nahi mila.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3">
                                Grand Total
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_applications']) }}
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
                                {{ number_format($data['total_applied']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_processing']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_approved']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_rejected']) }}
                            </td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_delivered']) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
