<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies;

use App\Filament\Resources\TravelInsurancePolicies\Pages\CreateTravelInsurancePolicy;
use App\Filament\Resources\TravelInsurancePolicies\Pages\EditTravelInsurancePolicy;
use App\Filament\Resources\TravelInsurancePolicies\Pages\ListTravelInsurancePolicies;
use App\Filament\Resources\TravelInsurancePolicies\Pages\ViewTravelInsurancePolicy;
use App\Filament\Resources\TravelInsurancePolicies\Schemas\TravelInsurancePolicyForm;
use App\Filament\Resources\TravelInsurancePolicies\Schemas\TravelInsurancePolicyInfolist;
use App\Filament\Resources\TravelInsurancePolicies\Tables\TravelInsurancePoliciesTable;
use App\Models\TravelInsurancePolicy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TravelInsurancePolicyResource extends Resource
{
    protected static ?string $model =
        TravelInsurancePolicy::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-document-check';

    protected static string|UnitEnum|null $navigationGroup =
        'Travel Insurance';

    protected static ?string $navigationLabel =
        'Insurance Policies';

    protected static ?string $modelLabel =
        'Insurance Policy';

    protected static ?string $pluralModelLabel =
        'Insurance Policies';

    protected static ?string $recordTitleAttribute =
        'policy_no';

    protected static ?int $navigationSort =
        10;

    public static function form(
        Schema $schema,
    ): Schema {
        return TravelInsurancePolicyForm::configure(
            $schema,
        );
    }

    public static function infolist(
        Schema $schema,
    ): Schema {
        return TravelInsurancePolicyInfolist::configure(
            $schema,
        );
    }

    public static function table(
        Table $table,
    ): Table {
        return TravelInsurancePoliciesTable::configure(
            $table,
        );
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_view',
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_create',
        ) ?? false;
    }

    public static function canView(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_view',
        ) ?? false;
    }

    public static function canEdit(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_edit',
        ) ?? false;
    }

    public static function canDelete(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_delete',
        ) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasPermission(
            'travel_insurance_policies_delete',
        ) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListTravelInsurancePolicies::route(
                    '/',
                ),

            'create' =>
                CreateTravelInsurancePolicy::route(
                    '/create',
                ),

            'view' =>
                ViewTravelInsurancePolicy::route(
                    '/{record}',
                ),

            'edit' =>
                EditTravelInsurancePolicy::route(
                    '/{record}/edit',
                ),
        ];
    }
}