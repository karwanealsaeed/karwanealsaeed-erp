<?php

namespace App\Filament\Resources\StaffLeaves\Schemas;

use App\Models\LeaveType;
use App\Models\Staff;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StaffLeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Application')
                    ->columns(3)
                    ->schema([
                        TextInput::make('leave_no')
                            ->label('Leave No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('staff_id')
                            ->label('Staff Member')
                            ->options(fn (): array => Staff::query()
                                ->where('status', 'Active')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('leave_type_id')
                            ->label('Leave Type')
                            ->options(fn (): array => LeaveType::query()
                                ->where('status', 'Active')
                                ->orderBy('sort_order')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotalDays($get, $set)
                            ),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->live()
                            ->minDate(fn (Get $get) => $get('start_date'))
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotalDays($get, $set)
                            ),

                        Select::make('session')
                            ->options([
                                'Full Day' => 'Full Day',
                                'First Half' => 'First Half',
                                'Second Half' => 'Second Half',
                            ])
                            ->default('Full Day')
                            ->required()
                            ->live()
                            ->native(false)
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) =>
                                    self::syncTotalDays($get, $set)
                            ),

                        TextInput::make('total_days')
                            ->label('Total Days')
                            ->numeric()
                            ->suffix('Days')
                            ->disabled()
                            ->dehydrated()
                            ->default(1),

                        Select::make('status')
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Pending')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('applied_at')
                            ->label('Applied At')
                            ->default(now()),

                        Textarea::make('reason')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->disk('public')
                            ->directory('staff/leaves')
                            ->downloadable()
                            ->openable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),

                Section::make('Admin Review')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('approved_at')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('approver.name')
                            ->label('Approved By')
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('rejection_reason')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('admin_notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function syncTotalDays(Get $get, Set $set): void
    {
        $startDate = $get('start_date');
        $endDate = $get('end_date');

        if (blank($startDate) || blank($endDate)) {
            return;
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($end->lessThan($start)) {
            $set('total_days', null);

            return;
        }

        $days = $start->diffInDays($end) + 1;

        if ($days > 1) {
            $set('session', 'Full Day');
            $set('total_days', $days);

            return;
        }

        $set(
            'total_days',
            in_array(
                $get('session'),
                ['First Half', 'Second Half'],
                true
            ) ? 0.5 : 1
        );
    }
}