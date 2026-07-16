<x-filament-panels::page>
    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>SMTP Email Test</h2>
                <p>Send a test email to verify production SMTP settings.</p>
            </div>
        </div>

        <div class="kes-report-summary-grid">
            @foreach ($this->mailConfig() as $key => $value)
                <div class="kes-report-summary-card">
                    <span>{{ $key }}</span>
                    <strong style="font-size:15px;">{{ $value ?: '-' }}</strong>
                </div>
            @endforeach
        </div>

        <div class="kes-command-card">
            {{ $this->form }}
        </div>
    </div>
</x-filament-panels::page>