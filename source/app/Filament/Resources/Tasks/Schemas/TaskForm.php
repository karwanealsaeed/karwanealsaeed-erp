<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Task;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Information')
                    ->description(
                        'Create internal tasks and customer follow-ups.',
                    )
                    ->schema([
                        TextInput::make('title')
                            ->label('Task Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description / Instructions')
                            ->rows(5)
                            ->columnSpanFull(),

                        Select::make('task_type')
                            ->label('Task Type')
                            ->options(Task::typeOptions())
                            ->default('General')
                            ->required()
                            ->searchable()
                            ->native(false),

                        Select::make('priority')
                            ->options(Task::priorityOptions())
                            ->default(Task::PRIORITY_NORMAL)
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->options(Task::statusOptions())
                            ->default(Task::STATUS_PENDING)
                            ->required()
                            ->live()
                            ->native(false),

                        Toggle::make('is_private')
                            ->label('Private Task')
                            ->helperText(
                                'Private tasks should only be visible to authorized staff.',
                            )
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Assignment')
                    ->schema([
                        Select::make('assigned_to_id')
                            ->label('Assigned Staff')
                            ->relationship(
                                name: 'assignedTo',
                                titleAttribute: 'name',
                            )
                            ->searchable()
                            ->preload(),

                        Select::make('customer_id')
                            ->label('Related Customer')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'name',
                            )
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Schedule & Reminder')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Start Date & Time')
                            ->seconds(false),

                        DateTimePicker::make('due_at')
                            ->label('Due Date & Time')
                            ->seconds(false),

                        DateTimePicker::make('reminder_at')
                            ->label('Reminder Date & Time')
                            ->seconds(false)
                            ->helperText(
                                'Dashboard reminder phase mein is time ko use karega.',
                            ),
                    ])
                    ->columns(3),

                Section::make('Completion')
                    ->schema([
                        Textarea::make('completion_notes')
                            ->label('Completion Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(
                        fn (Get $get): bool =>
                            $get('status')
                            === Task::STATUS_COMPLETED,
                    ),
            ]);
    }
}