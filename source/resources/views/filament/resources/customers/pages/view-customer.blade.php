<x-filament-panels::page>
    @php
        $documentsRecords = $record->documents()->latest()->get();

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

        $canDownloadDocuments = auth()->user()?->hasPermission('customer_documents.download') ?? false;
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                        {{ $record->name }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Customer complete details and uploaded documents
                    </p>
                </div>

                <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">
                    {{ $record->status ?: '-' }}
                </span>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Customer Information
            </h3>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-sm font-semibold text-gray-500">Name</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Father Name</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->father_name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">CNIC</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->cnic ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Passport No</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->passport_no ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Mobile</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->mobile ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">WhatsApp</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->whatsapp ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Email</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->email ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Date of Birth</div>
                    <div class="text-base text-gray-950 dark:text-white">
                        {{ $record->date_of_birth ? $record->date_of_birth->format('d M Y') : '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Customer Type</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->customer_type ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">City</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->city ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Country</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->country ?: '-' }}</div>
                </div>
            </div>

            @if ($record->address)
                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-500">Address</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->address }}</div>
                </div>
            @endif

            @if ($record->notes)
                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-500">Notes</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->notes }}</div>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                    Customer Documents
                </h3>

                <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700">
                    {{ $documentsRecords->count() }} Records
                </span>
            </div>

            @if ($documentsRecords->count() > 0)
                <div class="grid gap-5">
                    @foreach ($documentsRecords as $documentRecord)
                        @php
                            $documents = $documentRecord->documents_list;
                        @endphp

                        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="font-bold text-gray-950 dark:text-white">
                                        {{ $documentRecord->title ?: $documentRecord->document_types_text }}
                                    </div>

                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        Service: {{ $documentRecord->service_type ?: '-' }}
                                        |
                                        Types: {{ $documentRecord->document_types_text }}
                                        |
                                        Files: {{ count($documents) }}
                                    </div>

                                    @if ($documentRecord->expiry_date)
                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            Expiry: {{ $documentRecord->expiry_date->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>

                                @if ($canDownloadDocuments && count($documents) > 0)
                                    <a
                                        href="{{ route('customer-documents.download', ['customerDocument' => $documentRecord, 'fileIndex' => 'all']) }}"
                                        target="_blank"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500"
                                    >
                                        Download All
                                    </a>
                                @endif
                            </div>

                            @if (count($documents) > 0)
                                <div class="overflow-x-auto">
                                    <table class="w-full border-collapse text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                                                <th class="px-3 py-2 font-semibold">#</th>
                                                <th class="px-3 py-2 font-semibold">File Name</th>
                                                <th class="px-3 py-2 font-semibold">Type</th>
                                                <th class="px-3 py-2 font-semibold">Size</th>
                                                <th class="px-3 py-2 font-semibold">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($documents as $index => $document)
                                                @php
                                                    $fileName = $document['file_name'] ?? '-';
                                                    $fileType = strtoupper((string) ($document['file_type'] ?? pathinfo($fileName, PATHINFO_EXTENSION)));
                                                    $fileSize = $formatSize($document['file_size'] ?? 0);
                                                @endphp

                                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                                    <td class="px-3 py-3">{{ $index + 1 }}</td>
                                                    <td class="px-3 py-3">{{ $fileName }}</td>
                                                    <td class="px-3 py-3">{{ $fileType ?: '-' }}</td>
                                                    <td class="px-3 py-3">{{ $fileSize }}</td>
                                                    <td class="px-3 py-3">
                                                        @if ($canDownloadDocuments)
                                                            <div class="flex flex-wrap gap-2">
                                                                <a
                                                                    href="{{ route('customer-documents.download', ['customerDocument' => $documentRecord, 'fileIndex' => $index, 'view' => 1]) }}"
                                                                    target="_blank"
                                                                    class="rounded-lg bg-blue-600 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-500"
                                                                >
                                                                    Open
                                                                </a>

                                                                <a
                                                                    href="{{ route('customer-documents.download', ['customerDocument' => $documentRecord, 'fileIndex' => $index]) }}"
                                                                    target="_blank"
                                                                    class="rounded-lg bg-green-600 px-3 py-1 text-xs font-semibold text-white hover:bg-green-500"
                                                                >
                                                                    Download
                                                                </a>
                                                            </div>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    Is record me koi file nahi.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Is customer ke documents upload nahi hue.
                </p>
            @endif
        </div>
    </div>
</x-filament-panels::page>