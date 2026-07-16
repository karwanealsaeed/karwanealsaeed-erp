<?php

namespace App\Filament\Resources\StaffAttendances\Schemas;

use App\Models\StaffMember;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Attendance Record')
                    ->description('Daily attendance, check-in and check-out record.')
                    ->columns(3)
                    ->schema([
                        Select::make('staff_member_id')
                            ->label('Staff Member')
                            ->options(fn () => StaffMember::query()
                                ->where('status', 'Active')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (StaffMember $staff): array => [
                                    $staff->id => $staff->display_name,
                                ])
                                ->toArray())
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        DatePicker::make('attendance_date')
                            ->label('Attendance Date')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->label('Attendance Status')
                            ->options([
                                'Present' => 'Present',
                                'Absent' => 'Absent',
                                'Leave' => 'Leave',
                                'Half Day' => 'Half Day',
                            ])
                            ->default('Present')
                            ->required()
                            ->native(false),

                        TimePicker::make('check_in')
                            ->label('Check In')
                            ->seconds(false),

                        TimePicker::make('check_out')
                            ->label('Check Out')
                            ->seconds(false),

                        TextInput::make('working_hours')
                            ->label('Working Hours')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}