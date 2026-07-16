<?php

namespace App\Filament\Resources\StaffPayrolls\Schemas;

use App\Models\StaffMember;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class StaffPayrollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee & Payroll Period')
                    ->description('Select employee and salary month.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('payroll_no')
                            ->label('Payroll No')
                            ->placeholder('Auto generated')
                            ->disabled()
                            ->dehydrated(false),

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
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set, Get $get): void {
                                $staff = StaffMember::find($state);

                                if ($staff) {
                                    $set('basic_salary', $staff->basic_salary);
                                }

                                self::calculateSalary($get, $set);
                            }),

                        Select::make('salary_month')
                            ->label('Salary Month')
                            ->required()
                            ->options([
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December',
                            ])
                            ->default((int) now()->format('m'))
                            ->native(false),

                        TextInput::make('salary_year')
                            ->label('Salary Year')
                            ->numeric()
                            ->default((int) now()->format('Y'))
                            ->required(),
                    ]),

                Section::make('Salary Breakdown')
                    ->description('Enter earning and deduction details.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('allowance')
                            ->label('Allowance')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('bonus')
                            ->label('Bonus')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('deduction')
                            ->label('Deduction')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('advance')
                            ->label('Advance')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('net_salary')
                            ->label('Net Salary')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(true),
                    ]),

                Section::make('Payment Details')
                    ->description('Record salary payment and balance.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('paid_amount')
                            ->label('Paid Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateSalary($get, $set)),

                        TextInput::make('balance_amount')
                            ->label('Balance Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix('PKR')
                            ->disabled()
                            ->dehydrated(true),

                        Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                'Unpaid' => 'Unpaid',
                                'Partial' => 'Partial',
                                'Paid' => 'Paid',
                                'Cancelled' => 'Cancelled',
                            ])
                            ->default('Unpaid')
                            ->disabled()
                            ->dehydrated(true),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required(
                                fn (Get $get): bool =>
                                    (float) (
                                        $get('paid_amount')
                                        ?: 0
                                    ) > 0
                            ),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'Cash' => 'Cash',
                                'Bank Transfer' => 'Bank Transfer',
                                'JazzCash' => 'JazzCash',
                                'EasyPaisa' => 'EasyPaisa',
                                'Cheque' => 'Cheque',
                            ])
                            ->native(false),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function calculateSalary(Get $get, Set $set): void
    {
        $basic = (float) ($get('basic_salary') ?: 0);
        $allowance = (float) ($get('allowance') ?: 0);
        $bonus = (float) ($get('bonus') ?: 0);
        $deduction = (float) ($get('deduction') ?: 0);
        $advance = (float) ($get('advance') ?: 0);
        $paid = (float) ($get('paid_amount') ?: 0);

        $net = max($basic + $allowance + $bonus - $deduction - $advance, 0);
        $balance = max($net - $paid, 0);

        $set('net_salary', $net);
        $set('balance_amount', $balance);

        if ($paid <= 0) {
            $set('status', 'Unpaid');
        } elseif ($balance <= 0) {
            $set('status', 'Paid');
        } else {
            $set('status', 'Partial');
        }
    }
}