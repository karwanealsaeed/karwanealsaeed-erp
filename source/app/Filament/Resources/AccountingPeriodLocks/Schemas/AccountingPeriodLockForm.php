<?php

namespace App\Filament\Resources\AccountingPeriodLocks\Schemas;

use App\Models\AccountingPeriodLock;
use App\Services\AccountingPeriodLockService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class AccountingPeriodLockForm
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->components([
                Section::make(
                    'Accounting Period'
                )
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Period Name')
                            ->placeholder(
                                'Example: July 2026 Closing'
                            )
                            ->required()
                            ->maxLength(255)
                            ->disabled(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): bool =>
                                    $record?->status
                                    === 'Locked'
                            )
                            ->dehydrated(),

                        Select::make('lock_scope')
                            ->label('Lock Scope')
                            ->options(
                                AccountingPeriodLockService::scopeOptions()
                            )
                            ->default(
                                AccountingPeriodLockService::SCOPE_ALL
                            )
                            ->required()
                            ->native(false)
                            ->disabled(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): bool =>
                                    $record?->status
                                    === 'Locked'
                            )
                            ->dehydrated(),

                        DatePicker::make('period_start')
                            ->label('Period Start')
                            ->required()
                            ->live()
                            ->disabled(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): bool =>
                                    $record?->status
                                    === 'Locked'
                            )
                            ->dehydrated(),

                        DatePicker::make('period_end')
                            ->label('Period End')
                            ->required()
                            ->minDate(
                                fn (
                                    Get $get
                                ) =>
                                    $get('period_start')
                            )
                            ->disabled(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): bool =>
                                    $record?->status
                                    === 'Locked'
                            )
                            ->dehydrated(),

                        Placeholder::make(
                            'current_status'
                        )
                            ->label('Current Status')
                            ->content(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): string =>
                                    $record?->status
                                    ?? 'Open'
                            ),

                        Placeholder::make(
                            'lock_information'
                        )
                            ->label('Lock Information')
                            ->content(
                                function (
                                    ?AccountingPeriodLock $record
                                ): string {
                                    if (
                                        ! $record
                                        || ! $record->locked_at
                                    ) {
                                        return 'Not Locked';
                                    }

                                    return 'Locked by '
                                        . (
                                            $record->locker?->name
                                            ?? 'User'
                                        )
                                        . ' on '
                                        . $record->locked_at
                                            ->format(
                                                'd M Y h:i A'
                                            );
                                }
                            ),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->disabled(
                                fn (
                                    ?AccountingPeriodLock $record
                                ): bool =>
                                    $record?->status
                                    === 'Locked'
                            )
                            ->dehydrated(),
                    ]),
            ]);
    }
}