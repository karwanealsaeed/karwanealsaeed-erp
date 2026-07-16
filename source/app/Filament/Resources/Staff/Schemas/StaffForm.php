<?php

namespace App\Filament\Resources\Staff\Schemas;

use App\Models\Department;
use App\Models\Designation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employment Information')
                    ->description('Staff department, designation aur job information.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('staff_code')
                            ->label('Staff Code')
                            ->placeholder('STF-00001')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        TextInput::make('employee_code')
                            ->label('Employee Code')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                                'Resigned' => 'Resigned',
                                'Terminated' => 'Terminated',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        Select::make('department_id')
                            ->label('Department')
                            ->options(fn (): array => Department::query()
                                ->where('status', 'Active')
                                ->orderBy('sort_order')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(
                                fn (Set $set): mixed => $set('designation_id', null)
                            )
                            ->native(false),

                        Select::make('designation_id')
                            ->label('Designation')
                            ->options(function (Get $get): array {
                                $departmentId = $get('department_id');

                                if (blank($departmentId)) {
                                    return [];
                                }

                                return Designation::query()
                                    ->where('department_id', $departmentId)
                                    ->where('status', 'Active')
                                    ->orderBy('sort_order')
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('department_id')))
                            ->native(false),

                        Select::make('employment_type')
                            ->label('Employment Type')
                            ->options([
                                'Permanent' => 'Permanent',
                                'Contract' => 'Contract',
                                'Part Time' => 'Part Time',
                                'Internship' => 'Internship',
                                'Daily Wages' => 'Daily Wages',
                            ])
                            ->default('Permanent')
                            ->native(false),

                        DatePicker::make('joining_date')
                            ->label('Joining Date')
                            ->default(now()),

                        DatePicker::make('leaving_date')
                            ->label('Leaving Date'),
                    ]),

                Section::make('Personal Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Staff Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('father_name')
                            ->label('Father / Husband Name')
                            ->maxLength(255),

                        TextInput::make('cnic')
                            ->label('CNIC')
                            ->maxLength(50),

                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(50),

                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),

                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth'),

                        Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                                'Other' => 'Other',
                            ])
                            ->native(false),

                        Select::make('marital_status')
                            ->label('Marital Status')
                            ->options([
                                'Single' => 'Single',
                                'Married' => 'Married',
                                'Divorced' => 'Divorced',
                                'Widowed' => 'Widowed',
                            ])
                            ->native(false),

                        FileUpload::make('photo')
                            ->label('Staff Photo')
                            ->image()
                            ->disk('public')
                            ->directory('staff/photos')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),

                Section::make('Salary Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('allowance')
                            ->label('Monthly Allowance')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('deduction')
                            ->label('Fixed Deduction')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0)
                            ->minValue(0),
                    ]),

                Section::make('Address & Emergency Contact')
                    ->columns(3)
                    ->schema([
                        TextInput::make('city')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->default('Pakistan')
                            ->maxLength(255),

                        TextInput::make('emergency_contact_name')
                            ->label('Emergency Contact Name')
                            ->maxLength(255),

                        TextInput::make('emergency_contact_phone')
                            ->label('Emergency Contact Phone')
                            ->tel()
                            ->maxLength(50),

                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}