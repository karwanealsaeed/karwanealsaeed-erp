<x-filament-panels::page>
    @php
        $summary = $this->summary();
        $incidents = $this->incidents();
        $logFiles = $this->logFiles();
        $rawLog = $this->rawLog();
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                        Structured Error Monitoring
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Duplicate grouping, severity, admin alerts, resolution status and safe raw-log inspection.
                    </p>
                </div>

                <div class="text-sm text-gray-500">
                    Showing latest 100 matching incidents
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ([
                ['label' => 'Total', 'value' => $summary['total'], 'class' => 'text-gray-950 dark:text-white'],
                ['label' => 'Unresolved', 'value' => $summary['unresolved'], 'class' => 'text-orange-600'],
                ['label' => 'Critical', 'value' => $summary['critical'], 'class' => 'text-red-600'],
                ['label' => 'Errors', 'value' => $summary['error'], 'class' => 'text-amber-600'],
                ['label' => 'Resolved', 'value' => $summary['resolved'], 'class' => 'text-green-600'],
                ['label' => 'Last 24 Hours', 'value' => $summary['last_24_hours'], 'class' => 'text-blue-600'],
            ] as $card)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        {{ $card['label'] }}
                    </div>

                    <div class="mt-2 text-2xl font-bold {{ $card['class'] }}">
                        {{ $card['value'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Status
                    </label>

                    <select
                        wire:model.live="statusFilter"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                    >
                        <option value="open">Unresolved</option>
                        <option value="resolved">Resolved</option>
                        <option value="all">All</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Severity
                    </label>

                    <select
                        wire:model.live="severityFilter"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                    >
                        <option value="all">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="error">Error</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                        Search
                    </label>

                    <input
                        wire:model.live.debounce.500ms="search"
                        type="search"
                        placeholder="Message, class, file or route"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950"
                    >
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead class="bg-gray-50 text-left dark:bg-gray-950">
                        <tr>
                            <th class="px-4 py-3">Severity</th>
                            <th class="px-4 py-3">Error</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Occurrences</th>
                            <th class="px-4 py-3">Last Seen</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($incidents as $incident)
                            <tr wire:key="error-incident-{{ $incident->id }}">
                                <td class="px-4 py-4 align-top">
                                    <span
                                        @class([
                                            'rounded-full px-3 py-1 text-xs font-bold uppercase',
                                            'bg-red-100 text-red-700' => $incident->severity === 'critical',
                                            'bg-amber-100 text-amber-700' => $incident->severity === 'error',
                                        ])
                                    >
                                        {{ $incident->severity }}
                                    </span>
                                </td>

                                <td class="max-w-md px-4 py-4 align-top">
                                    <div class="font-semibold text-gray-950 dark:text-white">
                                        {{ class_basename($incident->exception_class) }}
                                    </div>

                                    <div class="mt-1 break-words text-gray-700 dark:text-gray-300">
                                        {{ $incident->message }}
                                    </div>

                                    @if ($incident->route_name || $incident->request_path)
                                        <div class="mt-2 text-xs text-gray-500">
                                            {{ $incident->request_method }}
                                            {{ $incident->route_name ?: $incident->request_path }}
                                        </div>
                                    @endif
                                </td>

                                <td class="max-w-sm px-4 py-4 align-top text-xs text-gray-600 dark:text-gray-300">
                                    @if ($incident->file)
                                        <div class="break-all">
                                            {{ $incident->file }}:{{ $incident->line }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top font-bold">
                                    {{ number_format($incident->occurrences) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 align-top text-gray-600 dark:text-gray-300">
                                    {{ $incident->last_seen_at?->format('d M Y h:i A') }}
                                </td>

                                <td class="px-4 py-4 align-top">
                                    @if ($incident->resolved_at)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                            Resolved
                                        </span>
                                    @else
                                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                            Open
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 align-top">
                                    @if ($incident->resolved_at)
                                        <button
                                            type="button"
                                            wire:click="reopenIncident({{ $incident->id }})"
                                            class="rounded-lg bg-orange-600 px-3 py-2 text-xs font-semibold text-white"
                                        >
                                            Reopen
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="resolveIncident({{ $incident->id }})"
                                            class="rounded-lg bg-green-600 px-3 py-2 text-xs font-semibold text-white"
                                        >
                                            Resolve
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-4 py-10 text-center text-gray-500"
                                >
                                    No matching error incidents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                Log Files
            </h3>

            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @forelse ($logFiles as $file)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="break-all font-semibold text-gray-950 dark:text-white">
                            {{ $file['name'] }}
                        </div>

                        <div class="mt-2 text-xs text-gray-500">
                            {{ $file['size'] }}
                            ·
                            {{ $file['modified_at'] }}
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">
                        No log files found.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div>
                <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                    Raw Log Tail
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $rawLog['name'] ?: 'No file' }}
                </p>
            </div>

            <pre class="mt-4 max-h-[650px] overflow-auto whitespace-pre-wrap break-words rounded-xl bg-gray-950 p-5 text-xs leading-6 text-gray-100">{{ $rawLog['content'] }}</pre>
        </div>
    </div>
</x-filament-panels::page>