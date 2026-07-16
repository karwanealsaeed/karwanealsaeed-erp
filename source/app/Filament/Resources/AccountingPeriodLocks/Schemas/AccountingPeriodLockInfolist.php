<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccountingPeriodLockInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make(
                    'Accounting Period'
                )
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Period Name'),

                        TextEntry::make('period_label')
                            ->label('Period'),

                        TextEntry::make('lock_scope')
                            ->label('Lock Scope')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(
                                fn (
                                    string $state
                                ): string =>
                                    match ($state) {
                                        'Locked' =>
                                            'danger',

                                        'Unlocked' =>
                                            'warning',

                                        default =>
                                            'success',
                                    }
                            ),

                        TextEntry::make('creator.name')
                            ->label('Created By')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(
                                'd M Y h:i A'
                            ),

                        TextEntry::make('locker.name')
                            ->label('Locked By')
                            ->placeholder('-'),

                        TextEntry::make('locked_at')
                            ->label('Locked At')
                            ->dateTime(
                                'd M Y h:i A'
                            )
                            ->placeholder('-'),

                        TextEntry::make('unlocker.name')
                            ->label('Unlocked By')
                            ->placeholder('-'),

                        TextEntry::make('unlocked_at')
                            ->label('Unlocked At')
                            ->dateTime(
                                'd M Y h:i A'
                            )
                            ->placeholder('-'),

                        TextEntry::make('unlock_reason')
                            ->label('Unlock Reason')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}