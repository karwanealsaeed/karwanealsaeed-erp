<?php

namespace App\Filament\Pages;

use App\Models\CustomerDocument;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class ExpiringDocumentsReport extends Page
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';



    protected static string | UnitEnum | null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 21;

    protected static ?string $navigationLabel = 'Expiring Documents';

    protected string $view = 'filament.pages.expiring-documents-report';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?string $statusFilter = 'Expiring';

    public ?string $documentType = null;

    public function mount(): void
    {
        $this->fromDate = now()->toDateString();
        $this->toDate = now()->addDays(90)->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('reports.view') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('statusFilter')
                    ->label('Status')
                    ->options([
                        'Expiring' => 'Expiring Soon',
                        'Expired' => 'Expired',
                        'All' => 'All',
                    ])
                    ->live()
                    ->default('Expiring'),

                DatePicker::make('fromDate')
                    ->label('From Date')
                    ->native(false)
                    ->live(),

                DatePicker::make('toDate')
                    ->label('To Date')
                    ->native(false)
                    ->live(),

                Select::make('documentType')
                    ->label('Document Type')
                    ->options([
                        'Passport' => 'Passport',
                        'CNIC' => 'CNIC',
                        'Visa' => 'Visa',
                        'Ticket' => 'Ticket',
                        'Hotel Voucher' => 'Hotel Voucher',
                        'Umrah Document' => 'Umrah Document',
                        'Payment Receipt' => 'Payment Receipt',
                        'Other' => 'Other',
                    ])
                    ->searchable()
                    ->placeholder('All Types')
                    ->live(),
            ]);
    }

    public function getRows()
    {
        return CustomerDocument::query()
            ->with('customer')
            ->when($this->statusFilter === 'Expiring', function ($query): void {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '>=', $this->fromDate)
                    ->whereDate('expiry_date', '<=', $this->toDate);
            })
            ->when($this->statusFilter === 'Expired', function ($query): void {
                $query->whereNotNull('expiry_date')
                    ->whereDate('expiry_date', '<', now()->toDateString());
            })
            ->when($this->statusFilter === 'All', function ($query): void {
                $query->whereNotNull('expiry_date');
            })
            ->when($this->documentType, function ($query): void {
                $query->where(function ($subQuery): void {
                    $subQuery->where('document_type', 'like', '%' . $this->documentType . '%')
                        ->orWhere('document_types', 'like', '%' . $this->documentType . '%');
                });
            })
            ->orderBy('expiry_date')
            ->get();
    }
}




