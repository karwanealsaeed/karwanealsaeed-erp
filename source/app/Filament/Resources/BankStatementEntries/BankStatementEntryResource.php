<?php

namespace App\Filament\Resources\BankStatementEntries;

use App\Filament\Resources\BankStatementEntries\Pages\CreateBankStatementEntry;
use App\Filament\Resources\BankStatementEntries\Pages\EditBankStatementEntry;
use App\Filament\Resources\BankStatementEntries\Pages\ListBankStatementEntries;
use App\Filament\Resources\BankStatementEntries\Pages\ViewBankStatementEntry;
use App\Filament\Resources\BankStatementEntries\Schemas\BankStatementEntryForm;
use App\Filament\Resources\BankStatementEntries\Schemas\BankStatementEntryInfolist;
use App\Filament\Resources\BankStatementEntries\Tables\BankStatementEntriesTable;
use App\Models\BankStatementEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BankStatementEntryResource extends Resource
{
    protected static ?string $model =
        BankStatementEntry::class;

    protected static string|BackedEnum|null
        $navigationIcon =
            'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null
        $navigationGroup = 'Finance';

    protected static ?string $navigationLabel =
        'Bank Statement';

    protected static ?string $modelLabel =
        'Bank Statement Entry';

    protected static ?string $pluralModelLabel =
        'Bank Statement Entries';

    protected static ?string $recordTitleAttribute =
        'entry_no';

    protected static ?int $navigationSort = 41;

    public static function canEdit($record): bool
    {
        return $record->reconciliation_status
            !== 'Matched'
            && ! $record->isPeriodLocked();
    }

    public static function canDelete($record): bool
    {
        return $record->reconciliation_status
            !== 'Matched'
            && ! $record->isPeriodLocked();
    }
    public static function form(
        Schema $schema
    ): Schema {
        return BankStatementEntryForm::configure(
            $schema
        );
    }

    public static function infolist(
        Schema $schema
    ): Schema {
        return BankStatementEntryInfolist::configure(
            $schema
        );
    }

    public static function table(
        Table $table
    ): Table {
        return BankStatementEntriesTable::configure(
            $table
        );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListBankStatementEntries::route('/'),

            'create' =>
                CreateBankStatementEntry::route(
                    '/create'
                ),

            'view' =>
                ViewBankStatementEntry::route(
                    '/{record}'
                ),

            'edit' =>
                EditBankStatementEntry::route(
                    '/{record}/edit'
                ),
        ];
    }
}