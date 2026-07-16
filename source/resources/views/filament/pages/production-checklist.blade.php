<x-filament-panels::page>
    @php
        $checks = $this->checks();
        $good = collect($checks)->where('status', 'Good')->count();
        $warning = collect($checks)->where('status', 'Warning')->count();
        $danger = collect($checks)->where('status', 'Danger')->count();
    @endphp

    <div class="kes-report-card">
        <div class="kes-report-head">
            <div>
                <h2>Production Checklist</h2>
                <p>Before live deployment, review important security and server checks.</p>
            </div>

            <button onclick="window.print()">Print</button>
        </div>

        <div class="kes-report-summary-grid">
            <div class="kes-report-summary-card">
                <span>Good</span>
                <strong>{{ $good }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Warnings</span>
                <strong>{{ $warning }}</strong>
            </div>

            <div class="kes-report-summary-card">
                <span>Danger</span>
                <strong>{{ $danger }}</strong>
            </div>
        </div>

        <div class="kes-report-table-wrap">
            <table class="kes-report-table">
                <thead>
                    <tr>
                        <th>Check</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Message</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($checks as $check)
                        <tr>
                            <td>{{ $check['label'] }}</td>
                            <td>{{ $check['value'] }}</td>
                            <td>
                                <span class="kes-status-pill {{ strtolower($check['status']) }}">
                                    {{ $check['status'] }}
                                </span>
                            </td>
                            <td>{{ $check['message'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <br>

        <div class="kes-command-card">
            <h3>Production Commands</h3>

            <pre>composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache</pre>
        </div>
    </div>
</x-filament-panels::page>