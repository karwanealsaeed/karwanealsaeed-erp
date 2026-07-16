<?php

namespace App\Filament\Resources\UmrahPackages\Tables;

use App\Models\UmrahPackage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UmrahPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('package_image')
                    ->label('Pic')
                    ->disk('public')
                    ->square()
                    ->placeholder('-'),

                TextColumn::make('package_no')
                    ->label('Package Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group_code')
                    ->label('Group Code')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('flight_route')
                    ->label('Route')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('airline')
                    ->label('Airline')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('duration_nights')
                    ->label('Duration')
                    ->suffix(' Night(s)')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('seats_left')
                    ->label('Seats Left')
                    ->sortable(),

                TextColumn::make('sharing_price')
                    ->label('Sharing')
                    ->money('PKR')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Available' => 'success',
                        'Sold Out' => 'danger',
                        'Inactive' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('is_featured')
                    ->label('Featured')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'gray'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),

                Action::make('copy')
                    ->label('Copy Package')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function (UmrahPackage $record): void {
                        $copy = $record->replicate();

                        $copy->package_no = null;
                        $copy->group_code = null;
                        $copy->slug = null;
                        $copy->title = $record->title . ' Copy';
                        $copy->status = 'Inactive';
                        $copy->is_featured = false;
                        $copy->save();

                        Notification::make()
                            ->title('Package copied successfully')
                            ->success()
                            ->send();
                    })
                    ->hidden(fn (): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.create') ?? false)
                    )),

                EditAction::make()
                    ->hidden(fn (): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.edit') ?? false)
                    )),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! (
                        (auth()->user()?->isAdmin() ?? false)
                        || (auth()->user()?->hasPermission('umrah_bookings.delete') ?? false)
                    )),
            ])
            ->defaultSort('id', 'desc');
    }
}