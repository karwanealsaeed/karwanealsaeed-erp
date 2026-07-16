<?php

namespace App\Filament\Resources\StaffDocuments\Schemas;

use App\Models\Staff;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StaffDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Staff Document Information')
                    ->description('Staff identity, employment aur certificate documents.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('document_no')
                            ->label('Document Record No')
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

                        Select::make('document_type')
                            ->label('Document Type')
                            ->options([
                                'CNIC' => 'CNIC',
                                'Passport' => 'Passport',
                                'Employment Contract' => 'Employment Contract',
                                'Appointment Letter' => 'Appointment Letter',
                                'Education Certificate' => 'Education Certificate',
                                'Experience Certificate' => 'Experience Certificate',
                                'Police Verification' => 'Police Verification',
                                'Medical Certificate' => 'Medical Certificate',
                                'Driving Licence' => 'Driving Licence',
                                'Training Certificate' => 'Training Certificate',
                                'Other' => 'Other',
                            ])
                            ->searchable()
                            ->required()
                            ->native(false),

                        TextInput::make('title')
                            ->label('Document Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('document_number')
                            ->label('Document Number')
                            ->maxLength(255),

                        DatePicker::make('issue_date')
                            ->label('Issue Date'),

                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->minDate(fn ($get) => $get('issue_date')),

                        TextInput::make('issuing_authority')
                            ->label('Issuing Authority')
                            ->maxLength(255),

                        Select::make('status')
                            ->options([
                                'Active' => 'Active',
                                'Expired' => 'Expired',
                                'Archived' => 'Archived',
                            ])
                            ->default('Active')
                            ->required()
                            ->native(false),

                        FileUpload::make('file_path')
                            ->label('Document File')
                            ->disk('public')
                            ->directory('staff/documents')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}