<?php

namespace App\Filament\Resources\StaffDocuments\Tables;

use App\Models\StaffDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('document_no')
                    ->label('Record No')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('staff.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_type')
                    ->label('Document Type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('document_number')
                    ->label('Document Number')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('issue_date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('expiry_date')
                    ->date('d M Y')
                    ->placeholder('No Expiry')
                    ->sortable()
                    ->color(fn (StaffDocument $record): string =>
                        $record->is_expiring_soon
                            ? 'warning'
                            : (
                                $record->status === 'Expired'
                                    ? 'danger'
                                    : 'gray'
                            )
                    ),

                IconColumn::make('is_expiring_soon')
                    ->label('Expiring Soon')
                    ->boolean(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Expired' => 'danger',
                        'Archived' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('uploader.name')
                    ->label('Uploaded By')
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('staff_id')
                    ->label('Staff')
                    ->relationship('staff', 'name'),

                SelectFilter::make('document_type')
                    ->options([
                        'CNIC' => 'CNIC',
                        'Passport' => 'Passport',
                        'Employment Contract' => 'Employment Contract',
                        'Appointment Letter' => 'Appointment Letter',
                        'Education Certificate' => 'Education Certificate',
                        'Experience Certificate' => 'Experience Certificate',
                        'Medical Certificate' => 'Medical Certificate',
                        'Other' => 'Other',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'Active' => 'Active',
                        'Expired' => 'Expired',
                        'Archived' => 'Archived',
                    ]),

                Filter::make('expiring_soon')
                    ->label('Expiring Within 30 Days')
                    ->query(fn (Builder $query): Builder =>
                        $query
                            ->whereNotNull('expiry_date')
                            ->whereDate('expiry_date', '>=', today())
                            ->whereDate(
                                'expiry_date',
                                '<=',
                                today()->addDays(30)
                            )
                    ),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('open_document')
                    ->label('Open File')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(
                        fn (StaffDocument $record): ?string =>
                            filled($record->file_path)
                                ? asset('storage/' . $record->file_path)
                                : null
                    )
                    ->openUrlInNewTab()
                    ->visible(
                        fn (StaffDocument $record): bool =>
                            filled($record->file_path)
                    ),

                Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(
                        fn (StaffDocument $record): bool =>
                            $record->status !== 'Archived'
                    )
                    ->action(function (StaffDocument $record): void {
                        $record->update([
                            'status' => 'Archived',
                        ]);
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}