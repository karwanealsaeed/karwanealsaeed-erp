<?php

namespace App\Filament\Resources\AccountVouchers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountVoucherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Voucher Details')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('voucher_no'),
                        TextEntry::make('voucher_date')->date('d M Y'),
                        TextEntry::make('voucher_type')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('reference_no')->placeholder('-'),
                        TextEntry::make('party_name')->placeholder('-'),
                        TextEntry::make('total_debit')->money('PKR'),
                        TextEntry::make('total_credit')->money('PKR'),
                        IconEntry::make('is_balanced')->boolean(),
                        TextEntry::make('description')->columnSpanFull()->placeholder('-'),
                    ]),

                Section::make('Lines')
                    ->schema([
                        TextEntry::make('lines_summary')
                            ->state(function ($record): string {
                                return $record->lines()
                                    ->with('accountHead')
                                    ->get()
                                    ->map(function ($line): string {
                                        return ($line->accountHead?->display_name ?: '-')
                                            . ' | Debit: PKR ' . number_format((float) $line->debit, 2)
                                            . ' | Credit: PKR ' . number_format((float) $line->credit, 2);
                                    })
                                    ->implode("\n");
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}