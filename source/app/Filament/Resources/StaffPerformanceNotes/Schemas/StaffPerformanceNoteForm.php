<?php

namespace App\Filament\Resources\StaffPerformanceNotes\Schemas;

use App\Models\Staff;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffPerformanceNoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Performance Review Information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('note_no')
                            ->label('Note No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),

                        Select::make('staff_id')
                            ->label('Staff Member')
                            ->options(fn (): array => Staff::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),

                        Select::make('review_type')
                            ->label('Review Type')
                            ->options([
                                'Performance Review' => 'Performance Review',
                                'Appreciation' => 'Appreciation',
                                'Warning' => 'Warning',
                                'Probation Review' => 'Probation Review',
                                'Training Review' => 'Training Review',
                                'Promotion Review' => 'Promotion Review',
                                'General Note' => 'General Note',
                            ])
                            ->default('Performance Review')
                            ->required()
                            ->native(false),

                        DatePicker::make('review_date')
                            ->label('Review Date')
                            ->default(today())
                            ->required(),

                        DatePicker::make('next_review_date')
                            ->label('Next Review Date')
                            ->minDate(fn ($get) => $get('review_date')),

                        Select::make('rating')
                            ->label('Rating')
                            ->options([
                                '1.0' => '1.0 - Poor',
                                '2.0' => '2.0 - Needs Improvement',
                                '3.0' => '3.0 - Satisfactory',
                                '4.0' => '4.0 - Good',
                                '5.0' => '5.0 - Excellent',
                            ])
                            ->native(false),

                        TextInput::make('title')
                            ->label('Review Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('status')
                            ->options([
                                'Draft' => 'Draft',
                                'Final' => 'Final',
                            ])
                            ->default('Draft')
                            ->required()
                            ->native(false),

                        Textarea::make('strengths')
                            ->label('Strengths / Achievements')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('improvements')
                            ->label('Areas for Improvement')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('goals')
                            ->label('Future Goals / Targets')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('attachment')
                            ->label('Supporting Attachment')
                            ->disk('public')
                            ->directory('staff/performance-notes')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->downloadable()
                            ->openable()
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}