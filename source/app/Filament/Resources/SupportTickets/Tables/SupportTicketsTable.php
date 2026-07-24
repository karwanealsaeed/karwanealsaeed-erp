<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Filament\Resources\SupportTickets\SupportTicketResource;

use App\Models\SupportTicketReply;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('ticket_no')->label('Ticket No')->searchable()->sortable(),
                TextColumn::make('name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('mobile')->searchable(),
                TextColumn::make('service_type')->label('Service')->badge()->color('warning')->placeholder('-'),
                TextColumn::make('subject')->limit(35)->searchable(),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Urgent' => 'danger',
                        'High' => 'warning',
                        'Low' => 'gray',
                        default => 'info',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Closed' => 'success',
                        'In Progress' => 'warning',
                        'Waiting Customer' => 'info',
                        default => 'primary',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->dateTime('d M Y h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Waiting Customer' => 'Waiting Customer',
                        'Closed' => 'Closed',
                    ]),
                SelectFilter::make('priority')
                    ->options([
                        'Low' => 'Low',
                        'Normal' => 'Normal',
                        'High' => 'High',
                        'Urgent' => 'Urgent',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('warning')
                    ->visible(
                        fn ($record): bool =>
                            SupportTicketResource::canEdit($record),
                    )
                    ->schema([
                        Textarea::make('message')
                            ->label('Reply Message')
                            ->required()
                            ->rows(5),
                    ])
                    ->action(function ($record, array $data): void {
                        SupportTicketReply::create([
                            'support_ticket_id' => $record->id,
                            'user_id' => auth()->id(),
                            'message' => $data['message'],
                            'is_admin' => true,
                        ]);

                        if ($record->status !== 'Closed') {
                            $record->update(['status' => 'Waiting Customer']);
                        }
                    }),

                Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn ($record): bool =>
                            $record->status !== 'Closed'
                            && SupportTicketResource::canEdit($record),
                    )
                    ->action(fn ($record) => $record->update([
                        'status' => 'Closed',
                        'closed_at' => now(),
                    ])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(
                            fn (): bool =>
                                SupportTicketResource::canDeleteAny(),
                        ),
                ]),
            ]);
    }
}