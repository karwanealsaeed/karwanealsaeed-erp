<?php

namespace App\Filament\Resources\Staff\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StaffInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('department.name')
                    ->label('Department')
                    ->placeholder('-'),
                TextEntry::make('designation.name')
                    ->label('Designation')
                    ->placeholder('-'),
                TextEntry::make('staff_code')
                    ->placeholder('-'),
                TextEntry::make('employee_code')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('father_name')
                    ->placeholder('-'),
                TextEntry::make('cnic')
                    ->placeholder('-'),
                TextEntry::make('mobile')
                    ->placeholder('-'),
                TextEntry::make('whatsapp')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('date_of_birth')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('marital_status')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('city')
                    ->placeholder('-'),
                TextEntry::make('country'),
                TextEntry::make('joining_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('leaving_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('employment_type')
                    ->placeholder('-'),
                TextEntry::make('basic_salary')
                    ->numeric(),
                TextEntry::make('salary')
                    ->numeric(),
                TextEntry::make('allowance')
                    ->numeric(),
                TextEntry::make('deduction')
                    ->numeric(),
                TextEntry::make('emergency_contact_name')
                    ->placeholder('-'),
                TextEntry::make('emergency_contact_phone')
                    ->placeholder('-'),
                TextEntry::make('photo')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
