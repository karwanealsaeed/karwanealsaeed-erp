<?php

namespace App\Filament\Resources\Payments;

use App\Filament\Resources\Payments\Forms\PaymentForm;
use App\Filament\Resources\Payments\Pages\CreatePayment;
use App\Filament\Resources\Payments\Pages\EditPayment;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Payments\Tables\PaymentsTable;
use App\Models\Payment;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';


    protected static ?string $model = Payment::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Visa Payments';
    protected static ?string $modelLabel = 'Visa Payment';
    protected static ?string $pluralModelLabel = 'Visa Payments';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('visa_payments.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('visa_payments.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasPermission('visa_payments.edit') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasPermission('visa_payments.delete') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayment::route('/create'),
            'view' => ViewPayment::route('/{record}'),
            'edit' => EditPayment::route('/{record}/edit'),
        ];
    }
}




