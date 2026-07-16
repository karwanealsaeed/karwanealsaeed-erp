<?php

namespace App\Filament\Resources\StaffPayrolls\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffPayrollInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payroll Details')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('payroll_no')->label('Payroll No'),
                        TextEntry::make('staffMember.display_name')->label('Staff Member'),
                        TextEntry::make('period_text')->label('Salary Month'),
                        TextEntry::make('status')->badge(),

                        TextEntry::make('basic_salary')->money('PKR'),
                        TextEntry::make('allowance')->money('PKR'),
                        TextEntry::make('bonus')->money('PKR'),
                        TextEntry::make('deduction')->money('PKR'),
                        TextEntry::make('advance')->money('PKR'),
                        TextEntry::make('net_salary')->money('PKR'),
                        TextEntry::make('paid_amount')->money('PKR'),
                        TextEntry::make('balance_amount')->money('PKR'),

                        TextEntry::make('payment_date')->date('d M Y')->placeholder('-'),
                        TextEntry::make('payment_method')->placeholder('-'),
                        TextEntry::make('remarks')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}