<x-filament-panels::page>
    @php
        $customers = $this->getCustomers();
        $data = $this->getReportData();
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Customer Ledger Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Customer wise services, payments aur outstanding balance ka complete ledger.
                    </p>
                </div>

                <div class="w-full md:w-80">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Customer
                    </label>

                    <select
                        wire:model.live="customerId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">Select Customer</option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }} - {{ $customer->mobile }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        @if (! $data['customer'])
            <div class="rounded-xl bg-white p-10 text-center shadow dark:bg-gray-900">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Customer select karein
                </h3>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Ledger dekhne ke liye upar se customer select karein.
                </p>
            </div>
        @else
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-bold text-gray-900 dark:text-white">
                    Customer Information
                </h3>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $data['customer']->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mobile</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $data['customer']->mobile ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">CNIC</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $data['customer']->cnic ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Passport No</p>
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $data['customer']->passport_no ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
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
                        Outstanding Balance
                    </p>

                    <h3 class="text-2xl font-bold text-red-600">
                        PKR {{ number_format($data['total_balance'], 2) }}
                    </h3>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Service Wise Summary
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left">Service</th>
                                <th class="px-6 py-3 text-right">Records</th>
                                <th class="px-6 py-3 text-right">Total Amount</th>
                                <th class="px-6 py-3 text-right">Paid Amount</th>
                                <th class="px-6 py-3 text-right">Balance</th>
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
                                        PKR {{ number_format($row['total_amount'], 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-green-600">
                                        PKR {{ number_format($row['paid_amount'], 2) }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-red-600">
                                        PKR {{ number_format($row['balance_amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                                <td class="px-6 py-3">
                                    Grand Total
                                </td>

                                <td class="px-6 py-3 text-right">
                                    {{ number_format(collect($data['rows'])->sum('count')) }}
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

            <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
                <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Payment Transactions
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Service</th>
                                <th class="px-6 py-3 text-left">Reference</th>
                                <th class="px-6 py-3 text-left">Method</th>
                                <th class="px-6 py-3 text-left">Notes</th>
                                <th class="px-6 py-3 text-right">Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($data['transactions'] as $transaction)
                                <tr class="border-b border-gray-200 dark:border-gray-800">
                                    <td class="px-6 py-3">
                                        {{ $transaction['date'] ? \Carbon\Carbon::parse($transaction['date'])->format('d M Y') : '-' }}
                                    </td>

                                    <td class="px-6 py-3">
                                        {{ $transaction['service'] }}
                                    </td>

                                    <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $transaction['reference'] }}
                                    </td>

                                    <td class="px-6 py-3">
                                        {{ $transaction['method'] }}
                                    </td>

                                    <td class="px-6 py-3">
                                        {{ $transaction['notes'] }}
                                    </td>

                                    <td class="px-6 py-3 text-right font-semibold text-green-600">
                                        PKR {{ number_format($transaction['amount'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                                        Is customer ki abhi koi payment transaction nahi mili.
                                    </td>
                                </tr>
                            @endforelse

                            <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                                <td class="px-6 py-3" colspan="5">
                                    Total Payments
                                </td>

                                <td class="px-6 py-3 text-right text-green-700">
                                    PKR {{ number_format($data['total_paid'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
