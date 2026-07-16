<?php

namespace App\Filament\Resources\CustomerDocuments\Pages;

use App\Filament\Resources\CustomerDocuments\CustomerDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditCustomerDocument extends EditRecord
{
    protected static string $resource = CustomerDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (): bool => ! (auth()->user()?->hasPermission('customer_documents.delete') ?? false)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->prepareDocumentData($data);
    }

    private function prepareDocumentData(array $data): array
    {
        $filePaths = $data['uploaded_files'] ?? [];

        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        if (! is_array($filePaths)) {
            $filePaths = [];
        }

        $documents = [];

        foreach ($filePaths as $filePath) {
            if (! is_string($filePath) || ! Storage::disk('public')->exists($filePath)) {
                continue;
            }

            $documents[] = [
                'file_name' => basename($filePath),
                'file_path' => $filePath,
                'file_type' => strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)),
                'file_size' => Storage::disk('public')->size($filePath),
            ];
        }

        $documentTypes = $data['document_types'] ?? [];

        if (is_string($documentTypes)) {
            $documentTypes = [$documentTypes];
        }

        $data['document_types'] = array_values($documentTypes);
        $data['document_type'] = implode(', ', $data['document_types']);
        $data['documents'] = $documents;

        $firstDocument = $documents[0] ?? null;

        if ($firstDocument) {
            $data['file_name'] = $firstDocument['file_name'];
            $data['file_path'] = $firstDocument['file_path'];
            $data['file_type'] = $firstDocument['file_type'];
            $data['file_size'] = $firstDocument['file_size'];
        }

        unset($data['uploaded_files']);

        return $data;
    }
}
