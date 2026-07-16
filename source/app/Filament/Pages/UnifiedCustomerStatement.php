<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Services\UnifiedCustomerStatementService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\URL;
use Throwable;
use UnitEnum;

class UnifiedCustomerStatement extends Page
{
    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-document-text';

    protected static string|UnitEnum|null
        $navigationGroup = 'Reports';

    protected static ?string $navigationLabel =
        'Customer Statement';

    protected static ?string $title =
        'Unified Customer Statement';

    protected static ?string $slug =
        'unified-customer-statement';

    protected static ?int $navigationSort = 24;

    protected string $view =
        'filament.pages.unified-customer-statement';

    public string $customerId = '';

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
                        $this->hasSelectedCustomer()
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
                        $this->hasSelectedCustomer()
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
                        $this->hasSelectedCustomer()
                        && filled(
                            $this
                                ->selectedCustomerWhatsApp()
                        )
                ),
        ];
    }

    public function customerOptions(): array
    {
        return Customer::query()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(
                fn (
                    Customer $customer
                ): array => [
                    $customer->id =>
                        $customer->name
                        . ' - '
                        . (
                            $customer->mobile
                            ?: 'No Mobile'
                        )
                        . ' ('
                        . $customer->customer_type
                        . ')',
                ]
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
            'Other' => 'Other',
        ];
    }

    public function resetFilters(): void
    {
        $this->customerId = '';

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
            UnifiedCustomerStatementService::class
        )->generate(
            customerId: $this->customerId,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            serviceType: $this->serviceType,
        );
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    private function hasSelectedCustomer(): bool
    {
        return filter_var(
            $this->customerId,
            FILTER_VALIDATE_INT
        ) !== false;
    }

    private function statementPdfUrl(
        string $disposition
    ): ?string {
        if (! $this->hasSelectedCustomer()) {
            return null;
        }

        $expiryHours = $this->shareExpiryHours();

        return URL::temporarySignedRoute(
            'customer-statements.pdf',
            now()->addHours($expiryHours),
            [
                'customer' =>
                    (int) $this->customerId,

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
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return null;
        }

        $phone = $this->normalizeWhatsAppNumber(
            $this->selectedCustomerWhatsApp()
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
                . $customer->name
                . ',',

            '',

            'Aap ka customer statement '
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

    private function selectedCustomer(): ?Customer
    {
        if (! $this->hasSelectedCustomer()) {
            return null;
        }

        return Customer::query()
            ->find((int) $this->customerId);
    }

    private function selectedCustomerWhatsApp(): ?string
    {
        $customer = $this->selectedCustomer();

        if (! $customer) {
            return null;
        }

        $whatsApp = trim(
            (string) (
                $customer->getAttribute(
                    'whatsapp'
                )
                ?? ''
            )
        );

        if (filled($whatsApp)) {
            return $whatsApp;
        }

        $mobile = trim(
            (string) (
                $customer->mobile
                ?? ''
            )
        );

        return filled($mobile)
            ? $mobile
            : null;
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
                'customer_statement.default_whatsapp_country_code',
                '92'
            )
        ) ?? '92';

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode
                . substr($digits, 1);
        } elseif (
            strlen($digits) === 10
            && str_starts_with(
                $digits,
                '3'
            )
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
                    'customer_statement.share_expiry_hours',
                    24
                )
            )
        );
    }
}