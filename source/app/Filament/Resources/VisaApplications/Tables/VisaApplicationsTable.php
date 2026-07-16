<?php

namespace App\Filament\Resources\VisaApplications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisaApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('application_no')
                    ->label('Application No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country.name')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('visa_type.name')
                    ->label('Visa Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('passport_no')
                    ->label('Passport No')
                    ->searchable(),

                TextColumn::make('passport_status')
                    ->label('Passport')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'gray',
                        'Received' => 'info',
                        'Embassy' => 'warning',
                        'Ready' => 'success',
                        'Returned' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('passport_received_date')
                    ->label('Received')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('passport_return_date')
                    ->label('Returned')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('travel_date')
                    ->label('Travel Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('return_date')
                    ->label('Return Date')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('visa_fee')
                    ->label('Visa Fee')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('service_fee')
                    ->label('Service Fee')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1)
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Applied' => 'warning',
                        'Processing' => 'info',
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        'Delivered' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
