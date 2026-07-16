<?php

namespace App\Filament\Resources\StaffLedgerEntries\Tables;

use App\Models\StaffLedgerEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StaffLedgerEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('entry_date', 'desc')
            ->columns([
                TextColumn::make('entry_no')
                    ->label('Entry No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entry_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('staff.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staff.department.name')
                    ->label('Department')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entry_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Debit' => 'danger',
                        'Credit' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('credit')
                    ->label('Credit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff Member')
                    ->relationship('staff', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('category')
                    ->options([
                        'Salary' => 'Salary',
                        'Advance' => 'Advance',
                        'Loan' => 'Loan',
                        'Bonus' => 'Bonus',
                        'Commission' => 'Commission',
                        'Reimbursement' => 'Reimbursement',
                        'Deduction' => 'Deduction',
                        'Recovery' => 'Recovery',
                        'Payment' => 'Payment',
                        'Opening Balance' => 'Opening Balance',
                        'Adjustment' => 'Adjustment',
                        'Other' => 'Other',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffLedgerEntry $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (StaffLedgerEntry $record): void {
                        $record->update([
                            'status' => 'Approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'rejected_by' => null,
                            'rejected_at' => null,
                            'rejection_reason' => null,
                        ]);

                        Notification::make()
                            ->title('Staff ledger entry approved')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(
                        fn (StaffLedgerEntry $record): bool =>
                            $record->status === 'Pending'
                    )
                    ->action(function (
                        StaffLedgerEntry $record,
                        array $data
                    ): void {
                        $record->update([
                            'status' => 'Rejected',
                            'rejected_by' => auth()->id(),
                            'rejected_at' => now(),
                            'rejection_reason' =>
                                $data['rejection_reason'],
                            'approved_by' => null,
                            'approved_at' => null,
                        ]);

                        Notification::make()
                            ->title('Staff ledger entry rejected')
                            ->danger()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffLedgerEntry $record): bool =>
                            in_array(
                                $record->status,
                                ['Pending', 'Approved'],
                                true
                            )
                    )
                    ->action(function (StaffLedgerEntry $record): void {
                        $record->update([
                            'status' => 'Cancelled',
                            'approved_by' => null,
                            'approved_at' => null,
                        ]);

                        Notification::make()
                            ->title('Staff ledger entry cancelled')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(
                        fn (StaffLedgerEntry $record): bool =>
                            $record->status === 'Pending'
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}