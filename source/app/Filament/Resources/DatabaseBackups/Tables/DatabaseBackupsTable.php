<?php

namespace App\Filament\Resources\DatabaseBackups\Tables;

use App\Models\DatabaseBackup;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DatabaseBackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('backup_no')
                    ->label('Backup No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('database_name')
                    ->label('Database')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('formatted_file_size')
                    ->label('Size'),

                TextColumn::make('drive_status')
                    ->label('Google Drive')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Uploaded' => 'success',
                        'Pending' => 'warning',
                        'Failed' => 'danger',
                        'Disabled' => 'gray',
                        'Not Uploaded' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('drive_uploaded_at')
                    ->label('Drive Uploaded At')
                    ->dateTime('d M Y h:i A')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('drive_error')
                    ->label('Drive Error')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('user.name')
                    ->label('Created By')
                    ->placeholder('System')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                Action::make('open_drive')
                    ->label('Open Drive')
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->hidden(fn (DatabaseBackup $record): bool => blank($record->drive_web_view_link))
                    ->url(fn (DatabaseBackup $record): string => $record->drive_web_view_link)
                    ->openUrlInNewTab(),

                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('database_backups.download') ?? false))
                    ->url(fn (DatabaseBackup $record): string => route('database-backups.download', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('database_backups.delete') ?? false)),
            ])
            ->defaultSort('id', 'desc');
    }
}
