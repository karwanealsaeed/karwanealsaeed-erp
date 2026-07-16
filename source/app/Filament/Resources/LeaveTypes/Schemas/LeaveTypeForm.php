<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Leave Type Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),

                        TextInput::make('code')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),

                        TextInput::make('annual_quota')
                            ->label('Annual Quota')
                            ->helperText('0 ka matlab unlimited/manual quota.')
                            ->numeric()
                            ->suffix('Days')
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Toggle::make('is_paid')
                            ->label('Paid Leave')
                            ->default(true),

                        TextInput::make('attachment_required_after_days')
                            ->label('Attachment Required From')
                            ->numeric()
                            ->suffix('Days')
                            ->default(0)
                            ->minValue(0)
                            ->helperText('0 ka matlab attachment optional.'),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}