<x-filament-panels::page>
    @php
        $report = $this->getReportData();
        $summary = $report['summary'];
        $policies = $report['policies'];
        $filters = $report['filters'];
    @endphp

    <div class="space-y-6">
        <form
            method="GET"
            action="{{ route('filament.admin.pages.travel-insurance-report') }}"
            class="grid gap-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:grid-cols-2 xl:grid-cols-4"
        >
            <div>
                <label class="mb-1 block text-sm font-medium">
                    Date From
                </label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Date To
                </label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] }}"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Provider
                </label>

                <select
                    name="provider_id"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
                    <option value="">All Providers</option>

                    @foreach ($this->providerOptions() as $id => $name)
                        <option
                            value="{{ $id }}"
                            @selected((string) $filters['provider_id'] === (string) $id)
                        >
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Search
                </label>

                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    maxlength="100"
                    placeholder="Policy, customer, traveller..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Policy Status
                </label>

                <select
                    name="status"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
                    <option value="">All Statuses</option>

                    @foreach ($this->policyStatusOptions() as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['status'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Approval
                </label>

                <select
                    name="approval_status"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
                    <option value="">All Approvals</option>

                    @foreach ($this->approvalStatusOptions() as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['approval_status'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">
                    Payment Status
                </label>

                <select
                    name="payment_status"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                >
                    <option value="">All Payment Statuses</option>

                    @foreach ($this->paymentStatusOptions() as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($filters['payment_status'] === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('filament.admin.pages.travel-insurance-report') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold dark:border-gray-700"
                >
                    Reset
                </a>
            </div>
        </form>

        <x-report-actions />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                'Policies' => number_format($summary['policy_count']),
                'Active' => number_format($summary['active_count']),
                'Total Sales' => 'PKR '.number_format($summary['total_amount'], 2),
                'Received' => 'PKR '.number_format($summary['paid_amount'], 2),
                'Outstanding' => 'PKR '.number_format($summary['balance_amount'], 2),
                'Supplier Cost' => 'PKR '.number_format($summary['supplier_cost'], 2),
                'Profit' => 'PKR '.number_format($summary['profit_amount'], 2),
                'Approved' => number_format($summary['approved_count']),
                'Expiring Soon' => number_format($summary['expiring_soon_count']),
            ] as $label => $value)
                <article class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="text-sm text-gray-500">
                        {{ $label }}
                    </div>

                    <div class="mt-1 text-xl font-bold">
                        {{ $value }}
                    </div>
                </article>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-950">
                        <tr>
                            <th class="px-3 py-3 text-left">Policy</th>
                            <th class="px-3 py-3 text-left">Customer / Traveller</th>
                            <th class="px-3 py-3 text-left">Provider</th>
                            <th class="px-3 py-3 text-left">Destination</th>
                            <th class="px-3 py-3 text-left">Validity</th>
                            <th class="px-3 py-3 text-left">Status</th>
                            <th class="px-3 py-3 text-right">Total</th>
                            <th class="px-3 py-3 text-right">Paid</th>
                            <th class="px-3 py-3 text-right">Balance</th>
                            <th class="px-3 py-3 text-right">Profit</th>
                            <th class="px-3 py-3 text-left">Invoice</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($policies as $policy)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-3">
                                    <strong>{{ $policy->policy_no }}</strong>

                                    <div class="text-xs text-gray-500">
                                        {{ $policy->passport_no ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    <strong>{{ $policy->customer?->name ?: '-' }}</strong>

                                    <div class="text-xs text-gray-500">
                                        {{ $policy->traveller_name ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    {{ $policy->provider?->display_name ?: '-' }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $policy->destination_country ?: '-' }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3">
                                    {{ $policy->policy_start_date?->format('d M Y') ?: '-' }}
                                    <br>
                                    {{ $policy->policy_end_date?->format('d M Y') ?: '-' }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $policy->status }}
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        {{ $policy->approval_status }} /
                                        {{ $policy->payment_status }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    {{ number_format((float) $policy->total_amount, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    {{ number_format((float) $policy->paid_amount, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    {{ number_format((float) $policy->balance_amount, 2) }}
                                </td>

                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    {{ number_format((float) $policy->profit_amount, 2) }}
                                </td>

                                <td class="px-3 py-3">
                                    {{ $policy->invoice?->invoice_no ?: '-' }}

                                    @if ($policy->invoice)
                                        <div class="text-xs text-gray-500">
                                            {{ $policy->invoice->status }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="11"
                                    class="px-4 py-10 text-center text-gray-500"
                                >
                                    No Travel Insurance policies matched these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $policies->links() }}
        </div>
    </div>
</x-filament-panels::page>