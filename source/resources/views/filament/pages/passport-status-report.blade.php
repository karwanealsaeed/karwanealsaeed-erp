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
                        Passport Status Report
                    </h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Visa applications ke passports ka status wise report.
                    </p>
                </div>

                <div class="w-full md:w-64">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Passport Status
                    </label>

                    <select
                        wire:model.live="passportStatus"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="All">All Status</option>

                        @foreach ($data['statuses'] as $status)
                            <option value="{{ $status }}">
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Passports
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['total_passports']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pending
                </p>

                <h3 class="text-2xl font-bold text-yellow-600">
                    {{ number_format($data['pending_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Received
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    {{ number_format($data['received_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Embassy
                </p>

                <h3 class="text-2xl font-bold text-purple-600">
                    {{ number_format($data['embassy_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ready
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    {{ number_format($data['ready_count']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Returned
                </p>

                <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-200">
                    {{ number_format($data['returned_count']) }}
                </h3>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Passport Details
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Application No</th>
                            <th class="px-6 py-3 text-left">Customer</th>
                            <th class="px-6 py-3 text-left">Mobile</th>
                            <th class="px-6 py-3 text-left">Passport No</th>
                            <th class="px-6 py-3 text-left">Country</th>
                            <th class="px-6 py-3 text-left">Visa Type</th>
                            <th class="px-6 py-3 text-left">Passport Status</th>
                            <th class="px-6 py-3 text-left">Received Date</th>
                            <th class="px-6 py-3 text-left">Return Date</th>
                            <th class="px-6 py-3 text-left">Visa Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($data['rows'] as $row)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                    {{ $row['application_no'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['customer_name'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['mobile'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 font-semibold">
                                    {{ $row['passport_no'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['country'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['visa_type'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3 font-semibold">
                                    {{ $row['passport_status'] ?? '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['passport_received_date'] ? \Carbon\Carbon::parse($row['passport_received_date'])->format('d M Y') : '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['passport_return_date'] ? \Carbon\Carbon::parse($row['passport_return_date'])->format('d M Y') : '-' }}
                                </td>

                                <td class="px-6 py-3">
                                    {{ $row['visa_status'] ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                                    Is status me koi passport record nahi mila.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
