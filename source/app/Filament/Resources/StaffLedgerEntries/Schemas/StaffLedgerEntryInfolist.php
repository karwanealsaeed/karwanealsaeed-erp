<?php

namespace App\Filament\Resources\StaffLedgerEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffLedgerEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Staff Ledger Entry')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('entry_no')
                            ->label('Entry No'),

                        TextEntry::make('entry_date')
                            ->label('Entry Date')
                            ->date('d M Y'),

                        TextEntry::make('staff.name')
                            ->label('Staff Member'),

                        TextEntry::make('staff.department.name')
                            ->label('Department')
                            ->placeholder('-'),

                        TextEntry::make('staff.designation.name')
                            ->label('Designation')
                            ->placeholder('-'),

                        TextEntry::make('category')
                            ->badge(),

                        TextEntry::make('entry_type')
                            ->label('Entry Type')
                            ->badge(),

                        TextEntry::make('debit')
                            ->money('PKR'),

                        TextEntry::make('credit')
                            ->money('PKR'),

                        TextEntry::make('reference_no')
                            ->label('Reference No')
                            ->placeholder('-'),

                        TextEntry::make('reference_type')
                            ->label('Reference Type')
                            ->placeholder('-'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('description')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Approval Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->placeholder('-'),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('rejecter.name')
                            ->label('Rejected By')
                            ->placeholder('-'),

                        TextEntry::make('rejected_at')
                            ->label('Rejected At')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}