<?php

namespace App\Http\Controllers;

use App\Filament\Pages\AirlineWiseFlightReport;
use App\Filament\Pages\BookingStatusReport;
use App\Filament\Pages\CountryWiseVisaReport;
use App\Filament\Pages\CustomerLedgerReport;
use App\Filament\Pages\CustomerWiseSalesReport;
use App\Filament\Pages\DailyCashbookReport;
use App\Filament\Pages\DailyFinanceReport;
use App\Filament\Pages\DateRangeFinanceReport;
use App\Filament\Pages\ExpenseCategoryReport;
use App\Filament\Pages\HotelWiseReport;
use App\Filament\Pages\MonthlyFinanceReport;
use App\Filament\Pages\OutstandingBalanceReport;
use App\Filament\Pages\PassportStatusReport;
use App\Filament\Pages\PaymentMethodReport;
use App\Filament\Pages\RefundReport;
use App\Filament\Pages\RouteWiseFlightReport;
use App\Filament\Pages\ServiceWiseSalesReport;
use App\Filament\Pages\UmrahPackageWiseReport;
use App\Filament\Pages\VisaTypeWiseReport;
use App\Filament\Pages\YearlyFinanceReport;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportExportController extends Controller
{
    private array $reports = [
        'daily-finance-report' => [
            'title' => 'Daily Finance Report',
            'class' => DailyFinanceReport::class,
        ],
        'monthly-finance-report' => [
            'title' => 'Monthly Finance Report',
            'class' => MonthlyFinanceReport::class,
        ],
        'yearly-finance-report' => [
            'title' => 'Yearly Finance Report',
            'class' => YearlyFinanceReport::class,
        ],
        'date-range-finance-report' => [
            'title' => 'Date Range Finance Report',
            'class' => DateRangeFinanceReport::class,
        ],
        'daily-cashbook-report' => [
            'title' => 'Daily Cashbook Report',
            'class' => DailyCashbookReport::class,
        ],
        'service-wise-sales-report' => [
            'title' => 'Service Wise Sales Report',
            'class' => ServiceWiseSalesReport::class,
        ],
        'customer-wise-sales-report' => [
            'title' => 'Customer Wise Sales Report',
            'class' => CustomerWiseSalesReport::class,
        ],
        'customer-ledger-report' => [
            'title' => 'Customer Ledger Report',
            'class' => CustomerLedgerReport::class,
        ],
        'payment-method-report' => [
            'title' => 'Payment Method Report',
            'class' => PaymentMethodReport::class,
        ],
        'expense-category-report' => [
            'title' => 'Expense Category Report',
            'class' => ExpenseCategoryReport::class,
        ],
        'refund-report' => [
            'title' => 'Refund Report',
            'class' => RefundReport::class,
        ],
        'outstanding-balance-report' => [
            'title' => 'Outstanding Balance Report',
            'class' => OutstandingBalanceReport::class,
        ],
        'booking-status-report' => [
            'title' => 'Booking Status Report',
            'class' => BookingStatusReport::class,
        ],
        'country-wise-visa-report' => [
            'title' => 'Country Wise Visa Report',
            'class' => CountryWiseVisaReport::class,
        ],
        'visa-type-wise-report' => [
            'title' => 'Visa Type Wise Report',
            'class' => VisaTypeWiseReport::class,
        ],
        'passport-status-report' => [
            'title' => 'Passport Status Report',
            'class' => PassportStatusReport::class,
        ],
        'airline-wise-flight-report' => [
            'title' => 'Airline Wise Flight Report',
            'class' => AirlineWiseFlightReport::class,
        ],
        'route-wise-flight-report' => [
            'title' => 'Route Wise Flight Report',
            'class' => RouteWiseFlightReport::class,
        ],
        'umrah-package-wise-report' => [
            'title' => 'Umrah Package Wise Report',
            'class' => UmrahPackageWiseReport::class,
        ],
        'hotel-wise-report' => [
            'title' => 'Hotel Wise Report',
            'class' => HotelWiseReport::class,
        ],
    ];

    public function __invoke(Request $request, string $report, string $format)
    {
        // PHASE 16F-3C2 VISIBLE REPORT EXPORT START
        if (
            $request->isMethod('post')
            && $report === 'visible'
        ) {
            return $this->exportVisibleReport(
                $request,
                $format,
            );
        }
        // PHASE 16F-3C2 VISIBLE REPORT EXPORT END

        abort_unless(isset($this->reports[$report]), 404);
        abort_unless($request->user()?->canViewReports() ?? false, 403);

        if ($format === 'print') {
            abort_unless($request->user()?->canPrintReports() ?? false, 403);
        }

        if ($format === 'pdf') {
            abort_unless($request->user()?->canDownloadReportPdf() ?? false, 403);
        }

        if ($format === 'excel') {
            abort_unless($request->user()?->canDownloadReportExcel() ?? false, 403);
        }

        $config = $this->reports[$report];

        $page = app($config['class']);

        if (method_exists($page, 'mount')) {
            $page->mount();
        }

        foreach ([
            'reportDate',
            'reportMonth',
            'reportYear',
            'fromDate',
            'toDate',
            'customerId',
            'passportStatus',
        ] as $property) {
            if ($request->has($property) && property_exists($page, $property)) {
                $value = $request->input($property);
                $page->{$property} = $value === '' ? null : $value;
            }
        }

        $data = $page->getReportData();

        $company = CompanySetting::current();
        $logoSrc = $this->getCompanyLogoSrc($company);

        $exportData = $this->prepareExportData($data);

        $title = $config['title'];
        $fileName = Str::slug($title).'-'.now()->format('Ymd-His');

        if ($format === 'print') {
            return view('reports.export', [
                'title' => $title,
                'company' => $company,
                'logoSrc' => $logoSrc,
                'exportData' => $exportData,
                'generatedAt' => now()->format('d M Y h:i A'),
                'isPrint' => true,
            ]);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.export', [
                'title' => $title,
                'company' => $company,
                'logoSrc' => $logoSrc,
                'exportData' => $exportData,
                'generatedAt' => now()->format('d M Y h:i A'),
                'isPrint' => false,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName.'.pdf');
        }

        $html = view('reports.export', [
            'title' => $title,
            'company' => $company,
            'logoSrc' => $logoSrc,
            'exportData' => $exportData,
            'generatedAt' => now()->format('d M Y h:i A'),
            'isPrint' => false,
        ])->render();

        return response("\xEF\xBB\xBF".$html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function getCompanyLogoSrc(CompanySetting $company): ?string
    {
        if (! $company->logo) {
            return null;
        }

        $path = public_path('storage/'.$company->logo);

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $base64 = base64_encode(file_get_contents($path));

        return 'data:'.$mime.';base64,'.$base64;
    }

    private function prepareExportData(array $data): array
    {
        $summary = [];
        $tables = [];

        foreach ($data as $key => $value) {
            if ($key === 'customer' && $value) {
                $tables[] = $this->makeTable('Customer Information', [[
                    'name' => $value->name ?? '-',
                    'mobile' => $value->mobile ?? '-',
                    'cnic' => $value->cnic ?? '-',
                    'passport_no' => $value->passport_no ?? '-',
                    'email' => $value->email ?? '-',
                    'address' => $value->address ?? '-',
                ]]);

                continue;
            }

            if (in_array($key, ['rows', 'transactions', 'service_rows', 'status_rows'], true) && is_array($value)) {
                $tables[] = $this->makeTable($this->label($key), $value);

                continue;
            }

            if ($this->isSimpleValue($value)) {
                $summary[$this->label($key)] = $this->formatValue($value);
            }
        }

        return [
            'summary' => $summary,
            'tables' => $tables,
        ];
    }

    private function makeTable(string $title, array $rows): array
    {
        $formattedRows = [];

        foreach ($rows as $row) {
            if ($row instanceof Model) {
                $row = $row->toArray();
            }

            if (is_object($row) && method_exists($row, 'toArray')) {
                $row = $row->toArray();
            }

            if (! is_array($row)) {
                continue;
            }

            $formattedRow = [];

            foreach ($row as $key => $value) {
                if (is_array($value) || (is_object($value) && ! $value instanceof DateTimeInterface)) {
                    continue;
                }

                $formattedRow[$this->label($key)] = $this->formatValue($value);
            }

            $formattedRows[] = $formattedRow;
        }

        return [
            'title' => $title,
            'rows' => $formattedRows,
        ];
    }

    private function isSimpleValue(mixed $value): bool
    {
        return is_null($value)
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value)
            || $value instanceof DateTimeInterface;
    }

    private function formatValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d M Y');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_float($value)) {
            return number_format($value, 2);
        }

        if (is_int($value)) {
            return number_format($value);
        }

        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }

    private function label(string $key): string
    {
        return (string) Str::of($key)
            ->replace('_', ' ')
            ->title();
    }

    /**
     * Export only the report data already rendered for the authorized
     * Admin page. This preserves the active Livewire filters without
     * querying a different customer, staff member or accounting scope.
     */
    private function exportVisibleReport(
        Request $request,
        string $format,
    ): \Symfony\Component\HttpFoundation\Response {
        abort_unless(
            in_array($format, ['pdf', 'excel'], true),
            404,
        );

        // PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION START
        abort_unless(
            $request->user()?->canViewReports() ?? false,
            403,
        );

        if ($format === 'pdf') {
            abort_unless(
                $request->user()?->canDownloadReportPdf() ?? false,
                403,
            );
        }

        if ($format === 'excel') {
            abort_unless(
                $request->user()?->canDownloadReportExcel() ?? false,
                403,
            );
        }
        // PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION END

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'source_url' => ['required', 'string', 'max:2048'],
            'source_route' => ['required', 'string', 'max:255'],
            'html' => ['required', 'string', 'max:2500000'],
            'tables' => ['nullable', 'array', 'max:30'],
            'tables.*.title' => ['nullable', 'string', 'max:180'],
            'tables.*.headers' => ['nullable', 'array', 'max:100'],
            'tables.*.headers.*' => ['nullable', 'string', 'max:1000'],
            'tables.*.rows' => ['nullable', 'array', 'max:10000'],
            'text_rows' => ['nullable', 'array', 'max:1000'],
            'text_rows.*' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->authorizeVisibleReportSource(
            $request,
            (string) $payload['source_route'],
            (string) $payload['source_url'],
        );

        $safeTitle = trim(
            strip_tags((string) $payload['title']),
        );
        $safeTitle = $safeTitle !== ''
            ? $safeTitle
            : 'Admin Report';

        $filename = Str::slug(
            $safeTitle,
        );

        if ($filename === '') {
            $filename = 'admin-report';
        }

        $filename .= '-'.now()->format('Ymd-His');

        if ($format === 'excel') {
            return $this->visibleExcelResponse(
                $payload,
                $safeTitle,
                $filename,
            );
        }

        $safeHtml = $this->sanitizeVisibleReportHtml(
            (string) $payload['html'],
        );

        return Pdf::loadView(
            'reports.visible-export-pdf',
            [
                'title' => $safeTitle,
                'sourceUrl' => (string) $payload['source_url'],
                'content' => $safeHtml,
                'generatedAt' => now(),
            ],
        )
            ->setPaper('a4', 'landscape')
            ->download($filename.'.pdf');
    }

    private function authorizeVisibleReportSource(
        Request $request,
        string $sourceRoute,
        string $sourceUrl,
    ): void {
        abort_unless(
            str_starts_with(
                $sourceRoute,
                'filament.admin.pages.',
            ),
            403,
        );

        $sourceHost = parse_url(
            $sourceUrl,
            PHP_URL_HOST,
        );

        abort_unless(
            $sourceHost === null
            || strcasecmp(
                (string) $sourceHost,
                $request->getHost(),
            ) === 0,
            403,
        );

        $sourcePath = parse_url(
            $sourceUrl,
            PHP_URL_PATH,
        );

        abort_unless(
            is_string($sourcePath)
            && str_starts_with($sourcePath, '/admin'),
            403,
        );

        $subRequest =
            Request::create(
                $sourcePath,
                'GET',
            );

        try {
            $matchedRoute = app('router')
                ->getRoutes()
                ->match($subRequest);
        } catch (\Throwable) {
            abort(403);
        }

        abort_unless(
            (string) ($matchedRoute->getName() ?? '')
            === $sourceRoute,
            403,
        );

        $action = $matchedRoute->getActionName();

        if (
            is_string($action)
            && class_exists($action)
            && method_exists($action, 'canAccess')
        ) {
            abort_unless(
                (bool) $action::canAccess(),
                403,
            );
        }
    }

    private function sanitizeVisibleReportHtml(
        string $html,
    ): string {
        if (! class_exists(\DOMDocument::class)) {
            return '<p>Rendered report preview unavailable.</p>';
        }

        $document = new \DOMDocument(
            '1.0',
            'UTF-8',
        );

        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="utf-8" ?>'
            .'<div id="karwan-visible-export-root">'
            .$html
            .'</div>',
            LIBXML_HTML_NOIMPLIED
            | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);

        foreach (
            [
                'script',
                'style',
                'link',
                'meta',
                'iframe',
                'object',
                'embed',
                'form',
                'input',
                'select',
                'textarea',
                'button',
                'nav',
                'aside',
            ] as $tag
        ) {
            $nodes = $xpath->query('//'.$tag);

            if ($nodes === false) {
                continue;
            }

            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $allElements = $xpath->query('//*');

        if ($allElements !== false) {
            foreach (
                iterator_to_array($allElements) as $element
            ) {
                if (! $element instanceof \DOMElement) {
                    continue;
                }

                $className =
                    ' '.strtolower(
                        (string) $element->getAttribute(
                            'class',
                        ),
                    ).' ';

                if (
                    str_contains($className, ' no-print ')
                    || str_contains($className, '-no-print ')
                    || str_contains(
                        $className,
                        ' kes-export-action',
                    )
                    || str_contains(
                        $className,
                        ' fi-sidebar',
                    )
                    || str_contains(
                        $className,
                        ' fi-topbar',
                    )
                    || str_contains(
                        $className,
                        ' karwan-ai',
                    )
                ) {
                    $element->parentNode?->removeChild(
                        $element,
                    );

                    continue;
                }

                $removeAttributes = [];

                foreach (
                    iterator_to_array($element->attributes) as $attribute
                ) {
                    $name = strtolower(
                        (string) $attribute->nodeName,
                    );

                    if (
                        str_starts_with($name, 'on')
                        || str_starts_with($name, 'wire:')
                        || str_starts_with($name, 'x-')
                        || str_starts_with($name, '@')
                        || in_array(
                            $name,
                            [
                                'style',
                                'src',
                                'href',
                                'action',
                                'formaction',
                            ],
                            true,
                        )
                    ) {
                        $removeAttributes[] = $name;
                    }
                }

                foreach ($removeAttributes as $name) {
                    $element->removeAttribute($name);
                }
            }
        }

        $root = $document->getElementById(
            'karwan-visible-export-root',
        );

        if (! $root instanceof \DOMElement) {
            return '<p>Rendered report preview unavailable.</p>';
        }

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    private function visibleExcelResponse(
        array $payload,
        string $title,
        string $filename,
    ): Response {
        $tables = is_array($payload['tables'] ?? null)
            ? $payload['tables']
            : [];
        $textRows =
            is_array($payload['text_rows'] ?? null)
                ? $payload['text_rows']
                : [];

        $html =
            '<!doctype html><html><head>'
            .'<meta charset="utf-8">'
            .'<style>'
            .'body{font-family:Arial,sans-serif;}'
            .'h1{font-size:20px;}'
            .'h2{font-size:15px;margin-top:20px;}'
            .'table{border-collapse:collapse;width:100%;margin:10px 0 20px;}'
            .'th,td{border:1px solid #aeb9b4;padding:6px;text-align:left;vertical-align:top;}'
            .'th{background:#fff5bf;color:#064937;font-weight:700;}'
            .'</style></head><body>'
            .'<h1>'.$this->visibleExcelValue($title).'</h1>'
            .'<p>Generated: '
            .$this->visibleExcelValue(
                now()->format('Y-m-d H:i:s'),
            )
            .'</p>';

        foreach ($tables as $index => $table) {
            if (! is_array($table)) {
                continue;
            }

            $tableTitle = trim(
                (string) ($table['title'] ?? ''),
            );

            if ($tableTitle !== '') {
                $html .= '<h2>'
                    .$this->visibleExcelValue(
                        $tableTitle,
                    )
                    .'</h2>';
            }

            $headers =
                is_array($table['headers'] ?? null)
                    ? $table['headers']
                    : [];
            $rows =
                is_array($table['rows'] ?? null)
                    ? $table['rows']
                    : [];

            $html .= '<table>';

            if ($headers !== []) {
                $html .= '<thead><tr>';

                foreach ($headers as $header) {
                    $html .= '<th>'
                        .$this->visibleExcelValue(
                            $header,
                        )
                        .'</th>';
                }

                $html .= '</tr></thead>';
            }

            $html .= '<tbody>';

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $html .= '<tr>';

                foreach ($row as $cell) {
                    $html .= '<td>'
                        .$this->visibleExcelValue(
                            $cell,
                        )
                        .'</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        if ($tables === [] && $textRows !== []) {
            $html .= '<table><tbody>';

            foreach ($textRows as $row) {
                $html .= '<tr><td>'
                    .$this->visibleExcelValue($row)
                    .'</td></tr>';
            }

            $html .= '</tbody></table>';
        }

        $html .= '</body></html>';

        return response(
            "\xEF\xBB\xBF".$html,
            200,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.xls"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ],
        );
    }

    private function visibleExcelValue(
        mixed $value,
    ): string {
        $text = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                strip_tags((string) $value),
            ) ?? '',
        );

        if (
            preg_match('/^[=+\-@]/u', $text) === 1
        ) {
            $text = "'".$text;
        }

        return htmlspecialchars(
            $text,
            ENT_QUOTES
            | ENT_SUBSTITUTE,
            'UTF-8',
        );
    }
}
