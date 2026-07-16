<?php

namespace App\Filament\Resources\BankStatementEntries\Tables;

use App\Models\AccountLedger;
use App\Models\BankStatementEntry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankStatementEntriesTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'statement_date',
                'desc'
            )
            ->columns([
                TextColumn::make('entry_no')
                    ->label('Entry No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make(
                    'bankAccount.bank_name'
                )
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('statement_date')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make(
                    'transaction_type'
                )
                    ->label('Type')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            $state === 'Deposit'
                                ? 'success'
                                : 'danger'
                    ),

                TextColumn::make('deposit')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('withdrawal')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('description')
                    ->limit(40)
                    ->placeholder('-'),

                TextColumn::make(
                    'reconciliation_status'
                )
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                'Matched' => 'success',
                                'Ignored' => 'gray',
                                default => 'warning',
                            }
                    ),

                TextColumn::make(
                    'matchedLedger.voucher_no'
                )
                    ->label('Matched Voucher')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('matched_at')
                    ->label('Matched At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make(
                    'bank_account_id'
                )
                    ->label('Bank Account')
                    ->relationship(
                        'bankAccount',
                        'name'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'reconciliation_status'
                )
                    ->label('Status')
                    ->options([
                        'Unmatched' => 'Unmatched',
                        'Matched' => 'Matched',
                        'Ignored' => 'Ignored',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('match')
                    ->label('Match GL Entry')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->schema(
                        fn (
                            BankStatementEntry $record
                        ): array => [
                            Select::make(
                                'account_ledger_id'
                            )
                                ->label(
                                    'Matching GL Ledger Entry'
                                )
                                ->options(
                                    self::ledgerOptions(
                                        $record
                                    )
                                )
                                ->searchable()
                                ->required()
                                ->native(false)
                                ->helperText(
                                    'Same bank account, same amount aur ±7 days entries show ho rahi hain.'
                                ),
                        ]
                    )
                    ->visible(
                        fn (
                            BankStatementEntry $record
                        ): bool =>
                            $record
                                ->reconciliation_status
                            === 'Unmatched'
                            && ! $record->isPeriodLocked()
                    )
                    ->action(function (
                        BankStatementEntry $record,
                        array $data
                    ): void {
                        $record->update([
                            'matched_account_ledger_id' =>
                                $data[
                                    'account_ledger_id'
                                ],

                            'reconciliation_status' =>
                                'Matched',

                            'matched_by' =>
                                auth()->id(),

                            'matched_at' => now(),
                        ]);

                        Notification::make()
                            ->title(
                                'Statement entry matched'
                            )
                            ->success()
                            ->send();
                    }),

                Action::make('unmatch')
                    ->label('Unmatch')
                    ->icon('heroicon-o-link-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(
                        fn (
                            BankStatementEntry $record
                        ): bool =>
                            $record
                                ->reconciliation_status
                            === 'Matched'
                            && ! $record->isPeriodLocked()
                    )
                    ->action(function (
                        BankStatementEntry $record
                    ): void {
                        $record->update([
                            'matched_account_ledger_id' =>
                                null,

                            'reconciliation_status' =>
                                'Unmatched',

                            'matched_by' => null,
                            'matched_at' => null,
                        ]);

                        Notification::make()
                            ->title(
                                'Statement entry unmatched'
                            )
                            ->success()
                            ->send();
                    }),

                Action::make('ignore')
                    ->label('Ignore')
                    ->icon('heroicon-o-eye-slash')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (
                            BankStatementEntry $record
                        ): bool =>
                            $record
                                ->reconciliation_status
                            === 'Unmatched'
                            && ! $record->isPeriodLocked()
                    )
                    ->action(function (
                        BankStatementEntry $record
                    ): void {
                        $record->update([
                            'reconciliation_status' =>
                                'Ignored',
                        ]);
                    }),

                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(
                        fn (
                            BankStatementEntry $record
                        ): bool =>
                            $record
                                ->reconciliation_status
                            === 'Ignored'
                            && ! $record->isPeriodLocked()
                    )
                    ->action(function (
                        BankStatementEntry $record
                    ): void {
                        $record->update([
                            'reconciliation_status' =>
                                'Unmatched',
                        ]);
                    }),

                EditAction::make()
                    ->visible(
                        fn (
                            BankStatementEntry $record
                        ): bool =>
                            $record
                                ->reconciliation_status
                            !== 'Matched'
                    ),
            ]);
    }

    private static function ledgerOptions(
        BankStatementEntry $record
    ): array {
        $record->loadMissing('bankAccount');

        $accountHeadId =
            $record->bankAccount?->account_head_id;

        if (! $accountHeadId) {
            return [];
        }

        $amount = round(
            $record->amount,
            2
        );

        $amountColumn =
            (float) $record->deposit > 0
                ? 'debit'
                : 'credit';

        $oppositeColumn =
            $amountColumn === 'debit'
                ? 'credit'
                : 'debit';

        $dateFrom = $record->statement_date
            ->copy()
            ->subDays(7)
            ->format('Y-m-d');

        $dateTo = $record->statement_date
            ->copy()
            ->addDays(7)
            ->format('Y-m-d');

        $usedLedgerIds =
            BankStatementEntry::query()
                ->whereNotNull(
                    'matched_account_ledger_id'
                )
                ->when(
                    $record->matched_account_ledger_id,
                    fn ($query) => $query->where(
                        'matched_account_ledger_id',
                        '!=',
                        $record
                            ->matched_account_ledger_id
                    )
                )
                ->pluck(
                    'matched_account_ledger_id'
                );

        return AccountLedger::query()
            ->where(
                'account_head_id',
                $accountHeadId
            )
            ->where('status', 'Posted')
            ->whereBetween('entry_date', [
                $dateFrom,
                $dateTo,
            ])
            ->where(
                $amountColumn,
                $amount
            )
            ->where(
                $oppositeColumn,
                0
            )
            ->whereNotIn('id', $usedLedgerIds)
            ->orderBy('entry_date')
            ->get()
            ->mapWithKeys(
                function (
                    AccountLedger $ledger
                ) use (
                    $amountColumn
                ): array {
                    $amount = (float)
                        $ledger->{$amountColumn};

                    $label =
                        $ledger->entry_date
                            ->format('d M Y')
                        . ' | '
                        . (
                            $ledger->voucher_no
                            ?: 'No Voucher'
                        )
                        . ' | PKR '
                        . number_format(
                            $amount,
                            2
                        );

                    if (
                        filled($ledger->description)
                    ) {
                        $label .= ' | '
                            . str($ledger->description)
                                ->limit(50);
                    }

                    return [
                        $ledger->id => $label,
                    ];
                }
            )
            ->toArray();
    }
}