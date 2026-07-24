<?php

namespace App\Filament\Resources\CustomerDocuments\Tables;

use App\Filament\Resources\CustomerDocuments\CustomerDocumentResource;
use App\Models\CustomerDocument;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_type')
                    ->label('Service')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_types_text')
                    ->label('Document Types')
                    ->wrap(),

                TextColumn::make('documents_count')
                    ->label('Files')
                    ->badge(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap(),

                TextColumn::make('formatted_file_size')
                    ->label('Total Size'),

                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Expired' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime('d M Y h:i A')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->icon('heroicon-o-eye'),

                Action::make('open_all')
                    ->label('Open All')
                    ->icon('heroicon-o-folder-open')
                    ->color('info')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('customer_documents.download') ?? false))
                    ->url(fn (CustomerDocument $record): string => CustomerDocumentResource::getUrl('view', [
                        'record' => $record,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('download_all')
                    ->label('Download All')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('customer_documents.download') ?? false))
                    ->url(fn (CustomerDocument $record): string => route('customer-documents.download', [
                        'customerDocument' => $record,
                        'fileIndex' => 'all',
                    ]))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('customer_documents.edit') ?? false)),

                DeleteAction::make()
                    ->hidden(
                        fn (CustomerDocument $record): bool => ! CustomerDocumentResource::canDelete(
                            $record,
                        ),
                    ),
            ])
            ->defaultSort('id', 'desc');
    }
}
