<?php

namespace App\Filament\Resources\StaffAttendances\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffAttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attendance Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('staffMember.display_name')->label('Staff Member'),
                        TextEntry::make('attendance_date')->date('d M Y'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('check_in')->placeholder('-'),
                        TextEntry::make('check_out')->placeholder('-'),
                        TextEntry::make('working_hours')->numeric(2)->suffix(' Hours'),
                        TextEntry::make('remarks')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}