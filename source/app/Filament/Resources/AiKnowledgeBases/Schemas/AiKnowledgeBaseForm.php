<?php

namespace App\Filament\Resources\AiKnowledgeBases\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AiKnowledgeBaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Knowledge Information')
                    ->description('Keywords ke zariye Karwan AI correct answer select karega.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->label('Knowledge Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('category')
                            ->options([
                                'Greeting' => 'Greeting',
                                'Umrah' => 'Umrah',
                                'Visa' => 'Visa',
                                'Air Ticketing' => 'Air Ticketing',
                                'Hotel' => 'Hotel',
                                'Travel Insurance' => 'Travel Insurance',
                                'Pakistan Tours' => 'Pakistan Tours',
                                'Agent Ledger' => 'Agent Ledger',
                                'Payment' => 'Payment',
                                'Booking Tracking' => 'Booking Tracking',
                                'Refund' => 'Refund',
                                'Contact' => 'Contact',
                                'Support' => 'Support',
                            ])
                            ->searchable()
                            ->native(false),

                        Textarea::make('keywords')
                            ->label('Matching Keywords')
                            ->helperText('Keywords comma, new line, semicolon ya | se separate karein.')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(10)
                            ->required(),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Inactive' => 'Inactive',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),
                    ]),

                Section::make('AI Answers')
                    ->columns(1)
                    ->schema([
                        Textarea::make('answer_english')
                            ->label('English Answer')
                            ->rows(6),

                        Textarea::make('answer_urdu')
                            ->label('Urdu Answer')
                            ->rows(6),

                        Textarea::make('answer_roman_urdu')
                            ->label('Roman Urdu Answer')
                            ->rows(6),
                    ]),
            ]);
    }
}