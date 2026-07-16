<?php

namespace App\Filament\Widgets;

use App\Models\VisaApplication;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentVisaApplications extends TableWidget
{
    protected static ?string $heading = 'Recent Visa Applications';

    protected int|string|array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()?->hasPermission('visa_applications.view') ?? false;
    }
    public function table(Table $table): Table
    {
        return $table
            ->query(
                VisaApplication::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('application_no')
                    ->label('Application No')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer'),

                Tables\Columns\TextColumn::make('country.name')
                    ->label('Country'),

                Tables\Columns\TextColumn::make('visaType.name')
                    ->label('Visa Type'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'Draft',
                        'info' => 'Applied',
                        'warning' => 'Processing',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                        'primary' => 'Delivered',
                    ]),

                Tables\Columns\BadgeColumn::make('passport_status')
                    ->label('Passport')
                    ->colors([
                        'warning' => 'Pending',
                        'info' => 'Received',
                        'success' => 'Returned',
                    ]),

                Tables\Columns\TextColumn::make('balance_amount')
                    ->label('Balance')
                    ->money('PKR', divideBy: 1),
            ])
            ->paginated(false);
    }
}
