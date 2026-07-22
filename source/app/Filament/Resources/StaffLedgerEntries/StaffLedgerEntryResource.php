<?php

namespace App\Filament\Resources\StaffLedgerEntries;

use App\Filament\Resources\StaffLedgerEntries\Pages\CreateStaffLedgerEntry;
use App\Filament\Resources\StaffLedgerEntries\Pages\EditStaffLedgerEntry;
use App\Filament\Resources\StaffLedgerEntries\Pages\ListStaffLedgerEntries;
use App\Filament\Resources\StaffLedgerEntries\Pages\ViewStaffLedgerEntry;
use App\Filament\Resources\StaffLedgerEntries\Schemas\StaffLedgerEntryForm;
use App\Filament\Resources\StaffLedgerEntries\Schemas\StaffLedgerEntryInfolist;
use App\Filament\Resources\StaffLedgerEntries\Tables\StaffLedgerEntriesTable;
use App\Models\StaffLedgerEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StaffLedgerEntryResource extends Resource
{

    use \App\Filament\Concerns\HasRbacResourceAuthorization;
protected static ?string $model = StaffLedgerEntry::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup = 'HR';

    protected static ?string $navigationLabel = 'Staff Ledger';

    protected static ?string $modelLabel = 'Staff Ledger Entry';

    protected static ?string $pluralModelLabel = 'Staff Ledger';

    protected static ?string $recordTitleAttribute = 'entry_no';

    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return StaffLedgerEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffLedgerEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffLedgerEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStaffLedgerEntries::route('/'),
            'create' => CreateStaffLedgerEntry::route('/create'),
            'view' => ViewStaffLedgerEntry::route('/{record}'),
            'edit' => EditStaffLedgerEntry::route('/{record}/edit'),
        ];
    }
}