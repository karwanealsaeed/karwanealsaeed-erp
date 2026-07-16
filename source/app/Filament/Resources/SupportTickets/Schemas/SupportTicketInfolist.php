<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ticket')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ticket_no')->label('Ticket No'),
                        TextEntry::make('name')->label('Customer'),
                        TextEntry::make('mobile'),
                        TextEntry::make('email')->placeholder('-'),
                        TextEntry::make('service_type')->label('Service')->badge()->placeholder('-'),
                        TextEntry::make('priority')->badge(),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->dateTime('d M Y h:i A'),
                        TextEntry::make('closed_at')->dateTime('d M Y h:i A')->placeholder('-'),
                        TextEntry::make('subject')->columnSpanFull(),
                        TextEntry::make('message')->columnSpanFull(),
                    ]),

                Section::make('Replies')
                    ->schema([
                        TextEntry::make('replies_list')
                            ->label('')
                            ->state(function ($record): string {
                                $record->loadMissing('replies');

                                if ($record->replies->count() === 0) {
                                    return 'No replies yet.';
                                }

                                return $record->replies
                                    ->map(function ($reply): string {
                                        $by = $reply->is_admin ? 'Admin' : 'Customer';
                                        return $by . ' - ' . $reply->created_at->format('d M Y h:i A') . "\n" . $reply->message;
                                    })
                                    ->implode("\n\n-----------------------\n\n");
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}