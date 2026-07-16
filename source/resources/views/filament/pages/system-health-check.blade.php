<x-filament-panels::page>
    @php
        $checks = $this->getChecks();

        $total = collect($checks)->flatten(1)->count();
        $passed = collect($checks)->flatten(1)->filter(fn ($check) => $check['status'])->count();
        $failed = $total - $passed;
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                        System Health Check
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        ERP system ke important modules, routes, storage aur backup status.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                        Passed: {{ $passed }}
                    </span>

                    <span class="rounded-full bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                        Failed: {{ $failed }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Total Checks</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $total }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Passed</div>
                <div class="mt-2 text-2xl font-bold text-green-600">{{ $passed }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Failed</div>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ $failed }}</div>
            </div>
        </div>

        @foreach ($checks as $groupName => $groupChecks)
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                    {{ $groupName }}
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                                <th class="px-3 py-2 font-semibold">Check</th>
                                <th class="px-3 py-2 font-semibold">Status</th>
                                <th class="px-3 py-2 font-semibold">Message</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($groupChecks as $check)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-3 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $check['name'] }}
                                    </td>

                                    <td class="px-3 py-3">
                                        @if ($check['status'])
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                OK
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Issue
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $check['message'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>