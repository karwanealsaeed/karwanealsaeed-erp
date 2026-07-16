<?php

namespace App\Filament\Resources\BankStatementEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankStatementEntryInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Statement Entry')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('entry_no')
                            ->label('Entry No'),

                        TextEntry::make(
                            'bankAccount.display_name'
                        )
                            ->label('Bank Account'),

                        TextEntry::make('statement_date')
                            ->label('Statement Date')
                            ->date('d M Y'),

                        TextEntry::make(
                            'transaction_type'
                        )
                            ->label('Transaction Type')
                            ->badge(),

                        TextEntry::make('deposit')
                            ->money('PKR'),

                        TextEntry::make('withdrawal')
                            ->money('PKR'),

                        TextEntry::make('reference_no')
                            ->label('Reference No')
                            ->placeholder('-'),

                        TextEntry::make('cheque_no')
                            ->label('Cheque No')
                            ->placeholder('-'),

                        TextEntry::make('running_balance')
                            ->label('Running Balance')
                            ->money('PKR')
                            ->placeholder('-'),

                        TextEntry::make(
                            'reconciliation_status'
                        )
                            ->label('Status')
                            ->badge(),

                        TextEntry::make(
                            'matchedLedger.voucher_no'
                        )
                            ->label('Matched Voucher')
                            ->placeholder('-'),

                        TextEntry::make(
                            'matchedLedger.entry_date'
                        )
                            ->label(
                                'Matched Ledger Date'
                            )
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('matcher.name')
                            ->label('Matched By')
                            ->placeholder('-'),

                        TextEntry::make('matched_at')
                            ->label('Matched At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}