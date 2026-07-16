<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffMemberInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee Information')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('employee_no')->label('Employee No'),
                        TextEntry::make('name')->label('Employee Name'),
                        TextEntry::make('father_name')->placeholder('-'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('cnic')->label('CNIC')->placeholder('-'),
                        TextEntry::make('mobile')->placeholder('-'),
                        TextEntry::make('whatsapp')->placeholder('-'),
                        TextEntry::make('email')->placeholder('-'),
                        TextEntry::make('designation')->placeholder('-'),
                        TextEntry::make('department')->badge()->placeholder('-'),
                        TextEntry::make('joining_date')->date('d M Y')->placeholder('-'),
                        TextEntry::make('leaving_date')->date('d M Y')->placeholder('-'),
                        TextEntry::make('basic_salary')->money('PKR'),
                        TextEntry::make('payment_method')->placeholder('-'),
                    ]),

                Section::make('Bank & Address')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('bank_name')->placeholder('-'),
                        TextEntry::make('bank_account_no')->placeholder('-'),
                        TextEntry::make('address')->columnSpanFull()->placeholder('-'),
                        TextEntry::make('notes')->columnSpanFull()->placeholder('-'),
                    ]),
            ]);
    }
}