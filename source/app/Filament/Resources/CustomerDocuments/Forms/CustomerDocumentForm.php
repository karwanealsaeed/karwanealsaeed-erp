<?php

namespace App\Filament\Resources\CustomerDocuments\Forms;

use App\Models\CustomerDocument;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('service_type')
                    ->label('Service Type')
                    ->options([
                        'General' => 'General',
                        'Visa' => 'Visa',
                        'Flight' => 'Flight',
                        'Umrah' => 'Umrah',
                        'Hotel' => 'Hotel',
                    ])
                    ->default('General')
                    ->required(),

                Select::make('document_types')
                    ->label('Document Types')
                    ->multiple()
                    ->options([
                        'Passport' => 'Passport',
                        'CNIC' => 'CNIC',
                        'Visa' => 'Visa',
                        'Ticket' => 'Ticket',
                        'Hotel Voucher' => 'Hotel Voucher',
                        'Umrah Document' => 'Umrah Document',
                        'Payment Receipt' => 'Payment Receipt',
                        'Other' => 'Other',
                    ])
                    ->searchable()
                    ->preload()
                    ->required()
                    ->afterStateHydrated(function (Select $component, ?CustomerDocument $record): void {
                        if (! $record) {
                            return;
                        }

                        $types = $record->document_types;

                        if (is_array($types) && count($types) > 0) {
                            $component->state($types);
                            return;
                        }

                        if ($record->document_type) {
                            $component->state(array_filter(array_map('trim', explode(',', $record->document_type))));
                        }
                    }),

                TextInput::make('title')
                    ->label('Title')
                    ->maxLength(255)
                    ->placeholder('Example: Passport + CNIC / Complete Customer Docs'),

                FileUpload::make('uploaded_files')
                    ->label('Upload Documents')
                    ->disk('local')
                            ->visibility('private')
                    ->directory('customer-documents')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(10240)
                    ->multiple()
                    ->reorderable()
                    ->downloadable()
                    ->openable()
                    ->required()
                    ->afterStateHydrated(function (FileUpload $component, ?CustomerDocument $record): void {
                        if (! $record) {
                            return;
                        }

                        $paths = [];

                        foreach ($record->documents_list as $document) {
                            if (! empty($document['file_path'])) {
                                $paths[] = $document['file_path'];
                            }
                        }

                        if (count($paths) > 0) {
                            $component->state($paths);
                        }
                    })
                    ->columnSpanFull(),

                DatePicker::make('expiry_date')
                    ->label('Expiry Date')
                    ->native(false),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Active' => 'Active',
                        'Expired' => 'Expired',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->default('Active')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
