<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make('Bank Account')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('bank_name')
                            ->label('Bank'),

                        TextEntry::make('name')
                            ->label('Account Name'),

                        TextEntry::make('account_title')
                            ->label('Account Title')
                            ->placeholder('-'),

                        TextEntry::make('account_number')
                            ->label('Account Number')
                            ->placeholder('-'),

                        TextEntry::make('iban')
                            ->label('IBAN')
                            ->placeholder('-'),

                        TextEntry::make('branch')
                            ->placeholder('-'),

                        TextEntry::make('branch_code')
                            ->label('Branch Code')
                            ->placeholder('-'),

                        TextEntry::make('swift_code')
                            ->label('SWIFT / BIC')
                            ->placeholder('-'),

                        TextEntry::make(
                            'accountHead.code'
                        )
                            ->label('GL Code')
                            ->badge(),

                        TextEntry::make(
                            'accountHead.name'
                        )
                            ->label('GL Account'),

                        TextEntry::make('currency'),

                        TextEntry::make('opening_date')
                            ->label('Opening Date')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('opening_balance')
                            ->label('Opening Balance')
                            ->money('PKR'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}