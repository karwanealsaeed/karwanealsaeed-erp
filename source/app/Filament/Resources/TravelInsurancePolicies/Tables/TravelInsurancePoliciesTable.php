<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePolicies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TravelInsurancePoliciesTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->defaultSort(
                'created_at',
                'desc',
            )
            ->columns(
                [
                    TextColumn::make(
                        'policy_no',
                    )
                        ->label(
                            'Policy No.',
                        )
                        ->searchable()
                        ->copyable()
                        ->sortable(),

                    TextColumn::make(
                        'customer.name',
                    )
                        ->label(
                            'Customer',
                        )
                        ->searchable()
                        ->sortable(),

                    TextColumn::make(
                        'traveller_name',
                    )
                        ->label(
                            'Traveller',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'provider.name',
                    )
                        ->label(
                            'Provider',
                        )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->sortable(),

                    TextColumn::make(
                        'destination_country',
                    )
                        ->label(
                            'Destination',
                        )
                        ->searchable()
                        ->sortable(),

                    TextColumn::make(
                        'travel_start_date',
                    )
                        ->label(
                            'Travel Start',
                        )
                        ->date(
                            'd M Y',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'travel_end_date',
                    )
                        ->label(
                            'Travel End',
                        )
                        ->date(
                            'd M Y',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'total_amount',
                    )
                        ->label(
                            'Total',
                        )
                        ->formatStateUsing(
                            fn (
                                mixed $state,
                                $record,
                            ): string =>
                                $record->currency
                                .' '
                                .number_format(
                                    (float) $state,
                                    2,
                                ),
                        )
                        ->sortable(),

                    TextColumn::make(
                        'balance_amount',
                    )
                        ->label(
                            'Balance',
                        )
                        ->formatStateUsing(
                            fn (
                                mixed $state,
                                $record,
                            ): string =>
                                $record->currency
                                .' '
                                .number_format(
                                    (float) $state,
                                    2,
                                ),
                        )
                        ->sortable(),

                    TextColumn::make(
                        'payment_status',
                    )
                        ->badge()
                        ->color(
                            fn (string $state): string =>
                                match ($state) {
                                    'Paid' =>
                                        'success',

                                    'Partial' =>
                                        'warning',

                                    default =>
                                        'danger',
                                },
                        )
                        ->sortable(),

                    TextColumn::make(
                        'approval_status',
                    )
                        ->badge()
                        ->color(
                            fn (string $state): string =>
                                match ($state) {
                                    'Approved' =>
                                        'success',

                                    'Rejected' =>
                                        'danger',

                                    default =>
                                        'warning',
                                },
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

                                    'Expired' =>
                                        'warning',

                                    'Cancelled',
                                    'Rejected' =>
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
                        'insurance_provider_id',
                    )
                        ->label(
                            'Provider',
                        )
                        ->relationship(
                            'provider',
                            'name',
                        )
                        ->searchable()
                        ->preload(),

                    SelectFilter::make(
                        'status',
                    )
                        ->options(
                            [
                                'Draft' =>
                                    'Draft',

                                'Active' =>
                                    'Active',

                                'Expired' =>
                                    'Expired',

                                'Cancelled' =>
                                    'Cancelled',

                                'Rejected' =>
                                    'Rejected',
                            ],
                        ),

                    SelectFilter::make(
                        'approval_status',
                    )
                        ->options(
                            [
                                'Pending' =>
                                    'Pending',

                                'Approved' =>
                                    'Approved',

                                'Rejected' =>
                                    'Rejected',
                            ],
                        ),

                    SelectFilter::make(
                        'payment_status',
                    )
                        ->options(
                            [
                                'Unpaid' =>
                                    'Unpaid',

                                'Partial' =>
                                    'Partial',

                                'Paid' =>
                                    'Paid',
                            ],
                        ),
                ],
            )
            ->recordActions(
                [
                                        \Filament\Actions\Action::make(
                        'createOrSyncInvoice',
                    )
                        ->label(
                            fn (
                                \App\Models\TravelInsurancePolicy $record,
                            ): string =>
                                filled(
                                    $record->invoice_id,
                                )
                                    ? 'Sync Invoice'
                                    : 'Create Invoice',
                        )
                        ->icon(
                            'heroicon-o-document-currency-dollar',
                        )
                        ->color(
                            'primary',
                        )
                        ->requiresConfirmation()
                        ->modalHeading(
                            'Create or Synchronize Insurance Invoice',
                        )
                        ->modalDescription(
                            'Policy amounts, payment balance aur status invoice mein synchronize honge.',
                        )
                        ->visible(
                            fn (): bool =>
                                auth()->user()?->hasPermission(
                                    'travel_insurance_policies_invoice',
                                ) ?? false,
                        )
                        ->action(
                            function (
                                \App\Models\TravelInsurancePolicy $record,
                            ): void {
                                $invoice =
                                    app(
                                        \App\Services\TravelInsuranceInvoiceService::class,
                                    )->createOrSync(
                                        $record,
                                    );

                                \Filament\Notifications\Notification::make()
                                    ->title(
                                        'Insurance invoice synchronized',
                                    )
                                    ->body(
                                        $invoice->invoice_no,
                                    )
                                    ->success()
                                    ->send();
                            },
                        ),
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