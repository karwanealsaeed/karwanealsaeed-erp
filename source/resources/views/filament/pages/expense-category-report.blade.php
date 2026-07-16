<x-filament-panels::page>
    @php
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Expense Category Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        From {{ \Carbon\Carbon::parse($data['from_date'])->format('d M Y') }}
                        to {{ \Carbon\Carbon::parse($data['to_date'])->format('d M Y') }}
                    </p>
                </div>

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            From Date
                        </label>

                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                    </div>

                    <div class="w-full md:w-48">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Expense Records</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_records']) }}
                </h3>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
                <h3 class="text-2xl font-bold text-red-600">
                    PKR {{ number_format($data['total_expenses'], 2) }}
                </h3>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Category Wise Expense Breakdown
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="text-left px-6 py-3">Category</th>
                            <th class="text-left px-6 py-3">Code</th>
                            <th class="text-right px-6 py-3">Records</th>
                            <th class="text-right px-6 py-3">Total Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-6 py-3 font-medium">
                                    {{ $row['category_name'] }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['category_code'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format($row['expense_count']) }}
                                </td>

                                <td class="px-6 py-3 text-right font-semibold text-red-600">
                                    PKR {{ number_format($row['total_amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                                    Selected date range me koi expense nahi mila.
                                </td>
                            </tr>
                        @endforelse

                        <tr class="bg-gray-50 dark:bg-gray-800 font-bold">
                            <td class="px-6 py-3" colspan="2">Grand Total</td>

                            <td class="px-6 py-3 text-right">
                                {{ number_format($data['total_records']) }}
                            </td>

                            <td class="px-6 py-3 text-right text-red-700">
                                PKR {{ number_format($data['total_expenses'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
