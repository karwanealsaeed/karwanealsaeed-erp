<?php

namespace App\Filament\Resources\Designations\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Designation Information')
                    ->columns(2)
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->options(fn (): array => Department::query()
                                ->where('status', 'Active')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->native(false),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->maxLength(30)
                            ->unique(ignoreRecord: true),

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