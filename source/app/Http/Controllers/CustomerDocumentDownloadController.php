<?php

namespace App\Http\Controllers;

use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CustomerDocumentDownloadController extends Controller
{
    public function __invoke(Request $request, CustomerDocument $customerDocument, string $fileIndex = '0')
    {
        abort_unless(auth()->user()?->hasPermission('customer_documents.download') ?? false, 403);

        if ($fileIndex === 'all') {
            return $this->downloadAll($customerDocument);
        }

        $documents = $customerDocument->documents_list;
        $document = $documents[(int) $fileIndex] ?? null;

        abort_unless($document, 404);

        $filePath = $document['file_path'] ?? null;
        $fileName = $document['file_name'] ?? basename((string) $filePath);

        abort_unless($filePath && Storage::disk('public')->exists($filePath), 404);

        $path = Storage::disk('public')->path($filePath);

        if ($request->boolean('view')) {
            return response()->file($path);
        }

        return Storage::disk('public')->download($filePath, $fileName);
    }

    private function downloadAll(CustomerDocument $customerDocument)
    {
        abort_unless(class_exists(ZipArchive::class), 500, 'PHP Zip extension enabled nahi hai.');

        $documents = $customerDocument->documents_list;

        abort_unless(count($documents) > 0, 404);

        $tempDirectory = storage_path('app/temp');

        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0755, true);
        }

        $safeCustomerName = preg_replace('/[^A-Za-z0-9\-]+/', '-', $customerDocument->customer?->name ?: 'customer');
        $zipFileName = 'customer-documents-' . $safeCustomerName . '-' . $customerDocument->id . '.zip';
        $zipPath = $tempDirectory . DIRECTORY_SEPARATOR . $zipFileName;

        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'ZIP file create nahi ho saki.');
        }

        foreach ($documents as $index => $document) {
            $filePath = $document['file_path'] ?? null;

            if (! $filePath || ! Storage::disk('public')->exists($filePath)) {
                continue;
            }

            $fullPath = Storage::disk('public')->path($filePath);
            $fileName = $document['file_name'] ?? basename($filePath);

            $zip->addFile($fullPath, ($index + 1) . '-' . $fileName);
        }

        $zip->close();

        abort_unless(file_exists($zipPath), 404);

        return response()
            ->download($zipPath, $zipFileName)
            ->deleteFileAfterSend(true);
    }
}