<?php

declare(strict_types=1);

namespace App\Filament\Resources\InsuranceProviders\Tables;

use App\Models\InsuranceProvider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InsuranceProvidersTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->defaultSort(
                'name',
            )
            ->columns(
                [
                    TextColumn::make(
                        'company_name',
                    )
                        ->label(
                            'Provider',
                        )
                        ->formatStateUsing(
                            fn (
                                ?string $state,
                                InsuranceProvider $record,
                            ): string =>
                                $record->display_name,
                        )
                        ->description(
                            fn (
                                InsuranceProvider $record,
                            ): ?string =>
                                filled(
                                    $record->company_name,
                                )
                                    ? $record->name
                                    : null,
                        )
                        ->searchable(
                            [
                                'name',
                                'company_name',
                            ],
                        )
                        ->sortable(),

                    TextColumn::make(
                        'supplier.name',
                    )
                        ->label(
                            'Linked Supplier',
                        )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'contact_person',
                    )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'phone',
                    )
                        ->placeholder(
                            '-',
                        )
                        ->searchable(),

                    TextColumn::make(
                        'email',
                    )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'license_no',
                    )
                        ->label(
                            'License No.',
                        )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'policies_count',
                    )
                        ->label(
                            'Policies',
                        )
                        ->counts(
                            'policies',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'status',
                    )
                        ->badge()
                        ->color(
                            fn (string $state): string =>
                                match ($state) {
                                    'Active' =>
                                        'success',

                                    'Suspended' =>
                                        'danger',

                                    default =>
                                        'gray',
                                },
                        )
                        ->sortable(),

                    TextColumn::make(
                        'created_at',
                    )
                        ->dateTime(
                            'd M Y',
                        )
                        ->sortable()
                        ->toggleable(
                            isToggledHiddenByDefault: true,
                        ),
                ],
            )
            ->filters(
                [
                    SelectFilter::make(
                        'status',
                    )
                        ->options(
                            [
                                'Active' =>
                                    'Active',

                                'Inactive' =>
                                    'Inactive',

                                'Suspended' =>
                                    'Suspended',
                            ],
                        ),

                    SelectFilter::make(
                        'country',
                    )
                        ->options(
                            fn (): array =>
                                InsuranceProvider::query()
                                    ->whereNotNull(
                                        'country',
                                    )
                                    ->where(
                                        'country',
                                        '!=',
                                        '',
                                    )
                                    ->orderBy(
                                        'country',
                                    )
                                    ->pluck(
                                        'country',
                                        'country',
                                    )
                                    ->all(),
                        )
                        ->searchable(),
                ],
            )
            ->recordActions(
                [
                    ViewAction::make(),

                    EditAction::make(),

                    DeleteAction::make()
                        ->requiresConfirmation(),
                ],
            )
            ->toolbarActions(
                [
                    BulkActionGroup::make(
                        [
                            DeleteBulkAction::make()
                                ->requiresConfirmation(),
                        ],
                    ),
                ],
            );
    }
}