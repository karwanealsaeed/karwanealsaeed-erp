<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsuranceProviders;

use App\Filament\Resources\InsuranceProviders\Pages\CreateInsuranceProvider;
use App\Filament\Resources\InsuranceProviders\Pages\EditInsuranceProvider;
use App\Filament\Resources\InsuranceProviders\Pages\ListInsuranceProviders;
use App\Filament\Resources\InsuranceProviders\Pages\ViewInsuranceProvider;
use App\Filament\Resources\InsuranceProviders\Schemas\InsuranceProviderForm;
use App\Filament\Resources\InsuranceProviders\Schemas\InsuranceProviderInfolist;
use App\Filament\Resources\InsuranceProviders\Tables\InsuranceProvidersTable;
use App\Models\InsuranceProvider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class InsuranceProviderResource extends Resource
{
    protected static ?string $model =
        InsuranceProvider::class;

    protected static string|BackedEnum|null $navigationIcon =
        'heroicon-o-shield-check';

    protected static string|UnitEnum|null $navigationGroup =
        'Master Data';

    protected static ?string $navigationLabel =
        'Insurance Providers';

    protected static ?string $modelLabel =
        'Insurance Provider';

    protected static ?string $pluralModelLabel =
        'Insurance Providers';

    protected static ?string $recordTitleAttribute =
        'name';

    protected static ?int $navigationSort =
        80;

    public static function form(
        Schema $schema,
    ): Schema {
        return InsuranceProviderForm::configure(
            $schema,
        );
    }

    public static function infolist(
        Schema $schema,
    ): Schema {
        return InsuranceProviderInfolist::configure(
            $schema,
        );
    }

    public static function table(
        Table $table,
    ): Table {
        return InsuranceProvidersTable::configure(
            $table,
        );
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(
            'insurance_providers_view',
        ) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission(
            'insurance_providers_create',
        ) ?? false;
    }

    public static function canEdit(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'insurance_providers_edit',
        ) ?? false;
    }

    public static function canDelete(
        Model $record,
    ): bool {
        return auth()->user()?->hasPermission(
            'insurance_providers_delete',
        ) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasPermission(
            'insurance_providers_delete',
        ) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                ListInsuranceProviders::route(
                    '/',
                ),

            'create' =>
                CreateInsuranceProvider::route(
                    '/create',
                ),

            'view' =>
                ViewInsuranceProvider::route(
                    '/{record}',
                ),

            'edit' =>
                EditInsuranceProvider::route(
                    '/{record}/edit',
                ),
        ];
    }
}