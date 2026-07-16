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
                        Refund Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $data['formatted_from_date'] }} to {{ $data['formatted_to_date'] }}
                    </p>
                </div>

                <div class="grid w-full grid-cols-1 gap-4 md:w-auto md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            From Date
                        </label>

                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <div>
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
                    Total Refund Records
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_refunds']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Refund Amount
                </p>

                <h3 class="text-2xl font-bold text-orange-600">
                    PKR {{ number_format($data['total_refund_amount'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Paid Refunds
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    PKR {{ number_format($data['paid_amount'], 2) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pending Refunds
                </p>

                <h3 class="text-2xl font-bold text-yellow-600">
                    PKR {{ number_format($data['pending_amount'], 2) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pending
                </p>

                <h3 class="text-xl font-bold text-yellow-600">
                    {{ number_format($data['pending_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Approved
                </p>

                <h3 class="text-xl font-bold text-blue-600">
                    {{ number_format($data['approved_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Paid
                </p>

                <h3 class="text-xl font-bold text-green-600">
                    {{ number_format($data['paid_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Rejected
                </p>

                <h3 class="text-xl font-bold text-red-600">
                    {{ number_format($data['rejected_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cancelled
                </p>

                <h3 class="text-xl font-bold text-gray-600">
                    {{ number_format($data['cancelled_count']) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Service Type Wise Refunds
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left">Service Type</th>
                                <th class="px-6 py-3 text-right">Count</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($data['service_rows'] as $row)
                                <tr class="border-b border-gray-200 dark:border-gray-800">
                                    <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $row['service_type'] }}
                                    </td>

                                    <td class="px-6 py-3 text-right">
                                        {{ number_format($row['count']) }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-orange-600">
                                        PKR {{ number_format($row['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                                <td class="px-6 py-3">Grand Total</td>
                                <td class="px-6 py-3 text-right">{{ number_format($data['total_refunds']) }}</td>
                                <td class="px-6 py-3 text-right text-orange-700">
                                    PKR {{ number_format($data['total_refund_amount'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Status Wise Refunds
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Count</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($data['status_rows'] as $row)
                                <tr class="border-b border-gray-200 dark:border-gray-800">
                                    <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $row['status'] }}
                                    </td>

                                    <td class="px-6 py-3 text-right">
                                        {{ number_format($row['count']) }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-orange-600">
                                        PKR {{ number_format($row['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                                <td class="px-6 py-3">Grand Total</td>
                                <td class="px-6 py-3 text-right">{{ number_format($data['total_refunds']) }}</td>
                                <td class="px-6 py-3 text-right text-orange-700">
                                    PKR {{ number_format($data['total_refund_amount'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Refund Details
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Refund No</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Customer</th>
                            <th class="px-6 py-3 text-left">Mobile</th>
                            <th class="px-6 py-3 text-left">Service</th>
                            <th class="px-6 py-3 text-left">Service Ref</th>
                            <th class="px-6 py-3 text-right">Amount</th>
                            <th class="px-6 py-3 text-left">Method</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Reason</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['refund_no'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['refund_date'] ? \Carbon\Carbon::parse($row['refund_date'])->format('d M Y') : '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['customer_name'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['mobile'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['service_type'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['service_reference'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-orange-600">
                                    PKR {{ number_format($row['refund_amount'], 2) }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['payment_method'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 font-semibold">
                                    {{ $row['status'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['reason'] ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                                    Is date range me koi refund record nahi mila.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                            <td class="px-6 py-3" colspan="6">Grand Total</td>

                            <td class="px-6 py-3 text-right text-orange-700">
                                PKR {{ number_format($data['total_refund_amount'], 2) }}
                            </td>

                            <td class="px-6 py-3" colspan="3"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>