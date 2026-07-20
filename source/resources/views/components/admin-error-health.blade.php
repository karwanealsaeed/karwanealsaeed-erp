<div
    class="mb-4 rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30"
>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="font-bold text-red-800 dark:text-red-200">
                Unresolved System Errors
            </div>

            <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                {{ $summary['unresolved'] }} unresolved incident(s),
                including {{ $summary['critical'] }} critical.
            </div>

            @foreach ($incidents as $incident)
                <div class="mt-2 text-xs text-red-700 dark:text-red-300">
                    <strong>{{ strtoupper($incident->severity) }}:</strong>
                    {{ \Illuminate\Support\Str::limit($incident->message, 120) }}
                </div>
            @endforeach
        </div>

        <a
            href="{{ route('filament.admin.pages.error-log-viewer') }}"
            class="inline-flex rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800"
        >
            Review Errors
        </a>
    </div>
</div>