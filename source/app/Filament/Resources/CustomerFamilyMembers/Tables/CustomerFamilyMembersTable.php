<?php

namespace App\Filament\Resources\CustomerFamilyMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerFamilyMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('customer.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('relationship')->badge()->placeholder('-'),
                TextColumn::make('cnic')->label('CNIC')->searchable()->placeholder('-'),
                TextColumn::make('passport_no')->label('Passport')->searchable()->placeholder('-'),
                TextColumn::make('date_of_birth')->date('d M Y')->placeholder('-'),
                TextColumn::make('mobile')->searchable()->placeholder('-'),
                TextColumn::make('status')->badge()->color('success'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ]),
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