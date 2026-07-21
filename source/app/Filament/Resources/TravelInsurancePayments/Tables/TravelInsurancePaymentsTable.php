<?php

declare(strict_types=1);

namespace App\Filament\Resources\TravelInsurancePayments\Tables;

use App\Filament\Resources\TravelInsurancePayments\TravelInsurancePaymentResource;
use App\Models\TravelInsurancePayment;
use App\Services\TravelInsurancePaymentApprovalService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TravelInsurancePaymentsTable
{
    public static function configure(
        Table $table,
    ): Table {
        return $table
            ->defaultSort(
                'payment_date',
                'desc',
            )
            ->columns(
                [
                    TextColumn::make(
                        'payment_no',
                    )
                        ->label(
                            'Payment No.',
                        )
                        ->searchable()
                        ->copyable()
                        ->sortable(),

                    TextColumn::make(
                        'policy.policy_no',
                    )
                        ->label(
                            'Policy No.',
                        )
                        ->searchable()
                        ->sortable(),

                    TextColumn::make(
                        'policy.customer.name',
                    )
                        ->label(
                            'Customer',
                        )
                        ->searchable(),

                    TextColumn::make(
                        'payment_date',
                    )
                        ->date(
                            'd M Y',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'amount',
                    )
                        ->formatStateUsing(
                            fn (
                                mixed $state,
                                TravelInsurancePayment $record,
                            ): string =>
                                ($record->policy?->currency ?? 'PKR')
                                .' '
                                .number_format(
                                    (float) $state,
                                    2,
                                ),
                        )
                        ->sortable(),

                    TextColumn::make(
                        'payment_method',
                    )
                        ->badge()
                        ->sortable(),

                    TextColumn::make(
                        'reference_no',
                    )
                        ->label(
                            'Reference No.',
                        )
                        ->placeholder(
                            '-',
                        )
                        ->searchable()
                        ->toggleable(),

                    TextColumn::make(
                        'status',
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
                        'posting_status',
                    )
                        ->label(
                            'Accounting',
                        )
                        ->badge()
                        ->color(
                            fn (string $state): string =>
                                $state === 'Posted'
                                    ? 'success'
                                    : 'gray',
                        )
                        ->sortable(),

                    TextColumn::make(
                        'approvedBy.name',
                    )
                        ->label(
                            'Reviewed By',
                        )
                        ->placeholder(
                            '-',
                        )
                        ->toggleable(
                            isToggledHiddenByDefault: true,
                        ),

                    TextColumn::make(
                        'approved_at',
                    )
                        ->label(
                            'Reviewed At',
                        )
                        ->dateTime(
                            'd M Y, h:i A',
                        )
                        ->placeholder(
                            '-',
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
                                'Pending' =>
                                    'Pending',

                                'Approved' =>
                                    'Approved',

                                'Rejected' =>
                                    'Rejected',
                            ],
                        ),

                    SelectFilter::make(
                        'posting_status',
                    )
                        ->label(
                            'Accounting Status',
                        )
                        ->options(
                            [
                                'Pending' =>
                                    'Pending',

                                'Posted' =>
                                    'Posted',

                                'Failed' =>
                                    'Failed',
                            ],
                        ),

                    SelectFilter::make(
                        'payment_method',
                    )
                        ->options(
                            [
                                'Cash' =>
                                    'Cash',

                                'Bank Transfer' =>
                                    'Bank Transfer',

                                'Card' =>
                                    'Card',

                                'Cheque' =>
                                    'Cheque',

                                'Online' =>
                                    'Online',

                                'Other' =>
                                    'Other',
                            ],
                        ),
                ],
            )
            ->recordActions(
                [
                    Action::make(
                        'approve',
                    )
                        ->label(
                            'Approve',
                        )
                        ->icon(
                            'heroicon-o-check-circle',
                        )
                        ->color(
                            'success',
                        )
                        ->requiresConfirmation()
                        ->modalHeading(
                            'Approve Insurance Payment',
                        )
                        ->modalDescription(
                            'Approved amount customer policy balance mein include ho jayegi.',
                        )
                        ->visible(
                            fn (
                                TravelInsurancePayment $record,
                            ): bool =>
                                TravelInsurancePaymentResource::canApprove(
                                    $record,
                                )
                                && $record->status !== 'Approved',
                        )
                        ->action(
                            function (
                                TravelInsurancePayment $record,
                            ): void {
                                app(
                                    TravelInsurancePaymentApprovalService::class,
                                )->approve(
                                    $record,
                                    auth()->user(),
                                );

                                Notification::make()
                                    ->title(
                                        'Insurance payment approved',
                                    )
                                    ->success()
                                    ->send();
                            },
                        ),

                    Action::make(
                        'reject',
                    )
                        ->label(
                            'Reject',
                        )
                        ->icon(
                            'heroicon-o-x-circle',
                        )
                        ->color(
                            'danger',
                        )
                        ->requiresConfirmation()
                        ->modalHeading(
                            'Reject Insurance Payment',
                        )
                        ->modalDescription(
                            'Rejected payment policy paid balance se remove ho jayegi.',
                        )
                        ->visible(
                            fn (
                                TravelInsurancePayment $record,
                            ): bool =>
                                TravelInsurancePaymentResource::canApprove(
                                    $record,
                                )
                                && $record->status !== 'Rejected',
                        )
                        ->action(
                            function (
                                TravelInsurancePayment $record,
                            ): void {
                                app(
                                    TravelInsurancePaymentApprovalService::class,
                                )->reject(
                                    $record,
                                    auth()->user(),
                                );

                                Notification::make()
                                    ->title(
                                        'Insurance payment rejected',
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
            );
    }
}