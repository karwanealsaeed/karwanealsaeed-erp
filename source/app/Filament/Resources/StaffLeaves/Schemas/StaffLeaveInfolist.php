<?php

namespace App\Filament\Resources\StaffLeaves\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffLeaveInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('leave_no')
                            ->label('Leave No'),

                        TextEntry::make('staff.name')
                            ->label('Staff Member'),

                        TextEntry::make('staff.department.name')
                            ->label('Department')
                            ->placeholder('-'),

                        TextEntry::make('leaveType.name')
                            ->label('Leave Type')
                            ->badge(),

                        TextEntry::make('start_date')
                            ->date('d M Y'),

                        TextEntry::make('end_date')
                            ->date('d M Y'),

                        TextEntry::make('total_days')
                            ->suffix(' Days'),

                        TextEntry::make('session')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('reason')
                            ->columnSpanFull(),

                        TextEntry::make('attachment')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Approval Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('approver.name')
                            ->label('Approved By')
                            ->placeholder('-'),

                        TextEntry::make('approved_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('rejecter.name')
                            ->label('Rejected By')
                            ->placeholder('-'),

                        TextEntry::make('rejected_at')
                            ->dateTime('d M Y h:i A')
                            ->placeholder('-'),

                        TextEntry::make('rejection_reason')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('admin_notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}