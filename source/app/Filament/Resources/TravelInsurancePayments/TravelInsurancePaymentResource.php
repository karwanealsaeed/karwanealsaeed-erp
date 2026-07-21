<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments;

use App\Filament\Resources\TravelInsurancePayments\Pages\CreateTravelInsurancePayment;
use App\Filament\Resources\TravelInsurancePayments\Pages\EditTravelInsurancePayment;
use App\Filament\Resources\TravelInsurancePayments\Pages\ListTravelInsurancePayments;
use App\Filament\Resources\TravelInsurancePayments\Pages\ViewTravelInsurancePayment;
use App\Filament\Resources\TravelInsurancePayments\Schemas\TravelInsurancePaymentForm;
use App\Filament\Resources\TravelInsurancePayments\Schemas\TravelInsurancePaymentInfolist;
use App\Filament\Resources\TravelInsurancePayments\Tables\TravelInsurancePaymentsTable;
use App\Models\TravelInsurancePayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TravelInsurancePaymentResource extends Resource
{
    protected static ?string $model =
        TravelInsurancePayment::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-banknotes';

    protected static string|UnitEnum|null $navigationGroup =
        'Travel Insurance';

    protected static ?string $navigationLabel =
        'Insurance Payments';

    protected static ?string $modelLabel =
        'Insurance Payment';

    protected static ?string $pluralModelLabel =
        'Insurance Payments';

    protected static ?string $recordTitleAttribute =
        'payment_no';

    protected static ?int $navigationSort =
        20;

    public static function form(
        Schema $schema,
    ): Schema {
        return TravelInsurancePaymentForm::configure(
            $schema,
        );
    }

    public static function infolist(
        Schema $schema,
    ): Schema {
        return TravelInsurancePaymentInfolist::configure(
            $schema,
        );
    }

    public static function table(
        Table $table,
    ): Table {
        return TravelInsurancePaymentsTable::configure(
            $table,
        );
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'travel_insurance_payments_view',
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(
            'travel_insurance_payments_create',
        ) ?? false;
    }

    public static function canView(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'travel_insurance_payments_view',
        ) ?? false;
    }

    public static function canEdit(
        Model $record,
    ): bool {
        if (
            ! auth()->user()?->hasPermission(
                'travel_insurance_payments_edit',
            )
        ) {
            return false;
        }

        return ! $record->isAccountingLocked();
    }

    public static function canDelete(
        Model $record,
    ): bool {
        if (
            ! auth()->user()?->hasPermission(
                'travel_insurance_payments_delete',
            )
        ) {
            return false;
        }

        return ! $record->isAccountingLocked();
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canApprove(
        TravelInsurancePayment $record,
    ): bool {
        if (
            ! auth()->user()?->hasPermission(
                'travel_insurance_payments_approve',
            )
        ) {
            return false;
        }

        return ! $record->isAccountingLocked();
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListTravelInsurancePayments::route(
                    '/',
                ),

            'create' =>
                CreateTravelInsurancePayment::route(
                    '/create',
                ),

            'view' =>
                ViewTravelInsurancePayment::route(
                    '/{record}',
                ),

            'edit' =>
                EditTravelInsurancePayment::route(
                    '/{record}/edit',
                ),
        ];
    }
}