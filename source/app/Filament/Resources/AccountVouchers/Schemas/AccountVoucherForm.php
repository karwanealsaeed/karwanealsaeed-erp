<?php

namespace App\Filament\Resources\AccountVouchers\Schemas;

use App\Models\AccountHead;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Details')
                    ->columns(4)
                    ->schema([
                        TextInput::make('voucher_no')
                            ->label('Voucher No')
                            ->disabled()
                            ->dehydrated(false),

                        DatePicker::make('voucher_date')
                            ->label('Voucher Date')
                            ->default(now())
                            ->required(),

                        Select::make('voucher_type')
                            ->required()
                            ->options([
                                'Receipt' => 'Receipt',
                                'Payment' => 'Payment',
                                'Journal' => 'Journal',
                                'Contra' => 'Contra',
                            ])
                            ->native(false),

                        Select::make('status')
                            ->required()
                            ->default('Posted')
                            ->options([
                                'Draft' => 'Draft',
                                'Posted' => 'Posted',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->native(false),

                        TextInput::make('reference_no')
                            ->label('Reference No'),

                        TextInput::make('party_name')
                            ->label('Party Name'),

                        Textarea::make('description')
                            ->columnSpan(2),
                    ]),

                Section::make('Debit / Credit Lines')
                    ->schema([
                        Repeater::make('lines')
                            ->relationship()
                            ->minItems(2)
                            ->defaultItems(2)
                            ->reorderable()
                            ->columns(5)
                            ->schema([
                                Select::make('account_head_id')
                                    ->label('Account')
                                    ->options(fn () => AccountHead::query()
                                        ->where('status', 'Active')
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn (AccountHead $account): array => [
                                            $account->id => $account->display_name,
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(2),

                                TextInput::make('debit')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('credit')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                TextInput::make('description')
                                    ->label('Line Note'),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}