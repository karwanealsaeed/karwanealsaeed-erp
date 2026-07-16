<?php

namespace App\Filament\Resources\UmrahVouchers\Tables;

use App\Models\UmrahVoucher;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahVouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('voucher_no')
                    ->label('Voucher No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('lead_guest')
                    ->label('Lead Guest')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.mobile')
                    ->label('Mobile')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('umrahPackage.package_no')
                    ->label('Package')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('travelers_text')
                    ->label('Travelers')
                    ->placeholder('-'),

                TextColumn::make('room_type')
                    ->label('Room Type')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('pnr')
                    ->label('PNR')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Issued' => 'success',
                        'Draft' => 'warning',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i A')
                    ->timezone('Asia/Karachi')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),

                Action::make('print')
                    ->label('Print Voucher')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (UmrahVoucher $record): string => route('umrah-vouchers.print', $record))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->hidden(fn (UmrahVoucher $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false)
                    )),

                DeleteAction::make()
                    ->hidden(fn (UmrahVoucher $record): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                    )),
            ])
            ->defaultSort('id', 'desc');
    }
}