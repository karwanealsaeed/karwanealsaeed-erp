<x-filament-panels::page>
    @php
        $documents = $record->documents_list;

        $documentTypes = $record->document_types;

        if (! is_array($documentTypes) || count($documentTypes) === 0) {
            $documentTypes = array_filter(array_map('trim', explode(',', (string) $record->document_type)));
        }

        $formatSize = function ($bytes) {
            $bytes = (int) $bytes;

            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            }

            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            }

            return $bytes . ' Bytes';
        };
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                        Customer Documents
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Details aur uploaded documents
                    </p>
                </div>

                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                    {{ $record->status }}
                </span>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Basic Information
            </h3>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-sm font-semibold text-gray-500">Customer</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->customer?->name ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Service Type</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->service_type ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Document Types</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ count($documentTypes) ? implode(', ', $documentTypes) : '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Title</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->title ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Expiry Date</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->expiry_date ? $record->expiry_date->format('d M Y') : '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Total Files</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ count($documents) }}
                    </div>
                </div>
            </div>

            @if ($record->notes)
                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-500">Notes</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->notes }}
                    </div>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                    Uploaded Documents
                </h3>

                @if (count($documents) > 0)
                    <a
                        href="{{ route('customer-documents.download', ['customerDocument' => $record, 'fileIndex' => 'all']) }}"
                        target="_blank"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500"
                    >
                        Download All
                    </a>
                @endif
            </div>

            @if (count($documents) > 0)
                <div class="grid gap-4">
                    @foreach ($documents as $index => $document)
                        @php
                            $fileName = $document['file_name'] ?? '-';
                            $fileType = strtoupper((string) ($document['file_type'] ?? pathinfo($fileName, PATHINFO_EXTENSION)));
                            $fileSize = $formatSize($document['file_size'] ?? 0);

                            $viewUrl = route('customer-documents.download', [
                                'customerDocument' => $record,
                                'fileIndex' => $index,
                                'view' => 1,
                            ]);

                            $downloadUrl = route('customer-documents.download', [
                                'customerDocument' => $record,
                                'fileIndex' => $index,
                            ]);

                            $isImage = in_array(strtolower($fileType), ['jpg', 'jpeg', 'png', 'webp']);
                            $isPdf = strtolower($fileType) === 'pdf';
                        @endphp

                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="font-bold text-gray-950 dark:text-white">
                                        {{ $index + 1 }}. {{ $fileName }}
                                    </div>

                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        Type: {{ $fileType ?: '-' }} | Size: {{ $fileSize }}
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a
                                        href="{{ $viewUrl }}"
                                        target="_blank"
                                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-500"
                                    >
                                        Open
                                    </a>

                                    <a
                                        href="{{ $downloadUrl }}"
                                        target="_blank"
                                        class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-500"
                                    >
                                        Download
                                    </a>
                                </div>
                            </div>

                            @if ($isImage)
                                <div class="mt-4">
                                    <img
                                        src="{{ $viewUrl }}"
                                        alt="{{ $fileName }}"
                                        class="max-h-96 rounded-lg border border-gray-200 object-contain dark:border-gray-700"
                                    >
                                </div>
                            @elseif ($isPdf)
                                <div class="mt-4">
                                    <iframe
                                        src="{{ $viewUrl }}"
                                        class="h-96 w-full rounded-lg border border-gray-200 dark:border-gray-700"
                                    ></iframe>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Koi document upload nahi hua.
                </p>
            @endif
        </div>
    </div>
</x-filament-panels::page>