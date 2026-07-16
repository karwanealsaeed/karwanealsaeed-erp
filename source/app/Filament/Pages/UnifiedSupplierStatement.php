<?php

namespace App\Filament\Pages;

use App\Services\UnifiedSupplierStatementService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Throwable;
use UnitEnum;

class UnifiedSupplierStatement extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-building-storefront';

    protected static string|UnitEnum|null
        $navigationGroup = 'Reports';

    protected static ?string $navigationLabel =
        'Supplier Statement';

    protected static ?string $title =
        'Unified Supplier Statement';

    protected static ?string $slug =
        'unified-supplier-statement';

    protected static ?int $navigationSort = 25;

    protected string $view =
        'filament.pages.unified-supplier-statement';

    public string $supplierId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $serviceType = 'all';

    public function mount(): void
    {
        $this->dateFrom = now()
            ->startOfYear()
            ->format('Y-m-d');

        $this->dateTo = today()
            ->format('Y-m-d');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'reports.view'
        ) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_pdf')
                ->label('View PDF')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->url(
                    fn (): string =>
                        $this->statementPdfUrl(
                            'inline'
                        ) ?? '#'
                )
                ->openUrlInNewTab()
                ->visible(
                    fn (): bool =>
                        $this->hasSelectedSupplier()
                ),

            Action::make('download_pdf')
                ->label('Download PDF')
                ->icon(
                    'heroicon-o-arrow-down-tray'
                )
                ->color('primary')
                ->url(
                    fn (): string =>
                        $this->statementPdfUrl(
                            'download'
                        ) ?? '#'
                )
                ->openUrlInNewTab()
                ->visible(
                    fn (): bool =>
                        $this->hasSelectedSupplier()
                ),

            Action::make('share_whatsapp')
                ->label('Share WhatsApp')
                ->icon(
                    'heroicon-o-chat-bubble-left-right'
                )
                ->color('success')
                ->url(
                    fn (): string =>
                        $this->whatsAppShareUrl()
                        ?? '#'
                )
                ->openUrlInNewTab()
                ->visible(
                    fn (): bool =>
                        $this->hasSelectedSupplier()
                        && filled(
                            $this
                                ->selectedSupplierWhatsApp()
                        )
                ),
        ];
    }

    public function supplierOptions(): array
    {
        if (! Schema::hasTable('suppliers')) {
            return [];
        }

        $nameColumn =
            $this->firstSupplierColumn([
                'name',
                'supplier_name',
                'company_name',
            ]);

        if (! $nameColumn) {
            return [];
        }

        $phoneColumn =
            $this->firstSupplierColumn([
                'whatsapp',
                'mobile',
                'phone',
                'contact_number',
            ]);

        return DB::table('suppliers')
            ->orderBy($nameColumn)
            ->get()
            ->mapWithKeys(
                function (
                    object $supplier
                ) use (
                    $nameColumn,
                    $phoneColumn
                ): array {
                    $name = (string) (
                        $supplier->{$nameColumn}
                        ?? 'Supplier'
                    );

                    $phone = $phoneColumn
                        ? trim(
                            (string) (
                                $supplier
                                    ->{$phoneColumn}
                                ?? ''
                            )
                        )
                        : '';

                    $label = $name;

                    if (filled($phone)) {
                        $label .= ' - ' . $phone;
                    }

                    return [
                        $supplier->id => $label,
                    ];
                }
            )
            ->toArray();
    }

    public function serviceTypeOptions(): array
    {
        return [
            'all' => 'All Services',
            'Visa' => 'Visa',
            'Flight' => 'Flight',
            'Umrah' => 'Umrah',
            'Hotel' => 'Hotel',
            'Transport' => 'Transport',
            'Other' => 'Other',
        ];
    }

    public function resetFilters(): void
    {
        $this->supplierId = '';

        $this->dateFrom = now()
            ->startOfYear()
            ->format('Y-m-d');

        $this->dateTo = today()
            ->format('Y-m-d');

        $this->serviceType = 'all';
    }

    public function reportData(): array
    {
        return app(
            UnifiedSupplierStatementService::class
        )->generate(
            supplierId: $this->supplierId,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            serviceType: $this->serviceType,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    private function hasSelectedSupplier(): bool
    {
        return filter_var(
            $this->supplierId,
            FILTER_VALIDATE_INT
        ) !== false;
    }

    private function statementPdfUrl(
        string $disposition
    ): ?string {
        if (! $this->hasSelectedSupplier()) {
            return null;
        }

        return URL::temporarySignedRoute(
            'supplier-statements.pdf',
            now()->addHours(
                $this->shareExpiryHours()
            ),
            [
                'supplier' =>
                    (int) $this->supplierId,

                'date_from' =>
                    $this->validDate(
                        $this->dateFrom,
                        now()
                            ->startOfYear()
                            ->format('Y-m-d')
                    ),

                'date_to' =>
                    $this->validDate(
                        $this->dateTo,
                        today()->format('Y-m-d')
                    ),

                'service_type' =>
                    filled($this->serviceType)
                        ? $this->serviceType
                        : 'all',

                'disposition' =>
                    $disposition,
            ]
        );
    }

    private function whatsAppShareUrl(): ?string
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier) {
            return null;
        }

        $phone = $this->normalizeWhatsAppNumber(
            $this->selectedSupplierWhatsApp()
        );

        if (! $phone) {
            return null;
        }

        $pdfUrl = $this->statementPdfUrl(
            'inline'
        );

        if (! $pdfUrl) {
            return null;
        }

        $supplierName =
            $this->selectedSupplierName()
            ?? 'Supplier';

        $fromDate = $this->displayDate(
            $this->dateFrom
        );

        $toDate = $this->displayDate(
            $this->dateTo
        );

        $brand = config(
            'customer_statement.company.brand',
            config('app.name')
        );

        $expiryHours =
            $this->shareExpiryHours();

        $message = implode("\n", [
            'Assalam-o-Alaikum '
                . $supplierName
                . ',',

            '',

            'Aap ka supplier statement '
                . $fromDate
                . ' se '
                . $toDate
                . ' tak tayyar hai.',

            '',

            'Statement PDF:',
            $pdfUrl,

            '',

            'Ye secure link '
                . $expiryHours
                . ' ghantay tak valid hai.',

            '',

            (string) $brand,
        ]);

        return 'https://wa.me/'
            . $phone
            . '?text='
            . rawurlencode($message);
    }

    private function selectedSupplier(): ?object
    {
        if (
            ! $this->hasSelectedSupplier()
            || ! Schema::hasTable('suppliers')
        ) {
            return null;
        }

        return DB::table('suppliers')
            ->where(
                'id',
                (int) $this->supplierId
            )
            ->first();
    }

    private function selectedSupplierName(): ?string
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier) {
            return null;
        }

        $nameColumn =
            $this->firstSupplierColumn([
                'name',
                'supplier_name',
                'company_name',
            ]);

        if (! $nameColumn) {
            return 'Supplier';
        }

        $name = trim(
            (string) (
                $supplier->{$nameColumn}
                ?? ''
            )
        );

        return filled($name)
            ? $name
            : 'Supplier';
    }

    private function selectedSupplierWhatsApp(): ?string
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier) {
            return null;
        }

        foreach (
            [
                'whatsapp',
                'mobile',
                'phone',
                'contact_number',
            ] as $column
        ) {
            if (
                ! Schema::hasColumn(
                    'suppliers',
                    $column
                )
            ) {
                continue;
            }

            $value = trim(
                (string) (
                    $supplier->{$column}
                    ?? ''
                )
            );

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private function firstSupplierColumn(
        array $columns
    ): ?string {
        if (! Schema::hasTable('suppliers')) {
            return null;
        }

        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    'suppliers',
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }

    private function normalizeWhatsAppNumber(
        ?string $phone
    ): ?string {
        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace(
            '/\D+/',
            '',
            (string) $phone
        ) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        $countryCode = preg_replace(
            '/\D+/',
            '',
            (string) config(
                'supplier_statement.default_whatsapp_country_code',
                '92'
            )
        ) ?? '92';

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode
                . substr($digits, 1);
        } elseif (
            strlen($digits) === 10
            && str_starts_with($digits, '3')
        ) {
            $digits = $countryCode
                . $digits;
        }

        $length = strlen($digits);

        if ($length < 10 || $length > 15) {
            return null;
        }

        return $digits;
    }

    private function validDate(
        string $value,
        string $fallback
    ): string {
        try {
            return Carbon::parse($value)
                ->format('Y-m-d');
        } catch (Throwable) {
            return $fallback;
        }
    }

    private function displayDate(
        string $value
    ): string {
        try {
            return Carbon::parse($value)
                ->format('d M Y');
        } catch (Throwable) {
            return $value;
        }
    }

    private function shareExpiryHours(): int
    {
        return min(
            168,
            max(
                1,
                (int) config(
                    'supplier_statement.share_expiry_hours',
                    24
                )
            )
        );
    }
}