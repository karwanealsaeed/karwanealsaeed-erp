<x-filament-panels::page>
    <div class="kes-log-card">
        <div class="kes-log-head">
            <div>
                <h2>Laravel Error Logs</h2>
                <p>Latest 400 lines from storage/logs/laravel.log</p>
            </div>
        </div>

        <pre class="kes-log-box">{{ $this->logs() }}</pre>
    </div>
</x-filament-panels::page>