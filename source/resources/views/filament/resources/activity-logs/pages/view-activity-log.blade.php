<x-filament-panels::page>
    @php
        $oldValues = $record->old_values ?? [];
        $newValues = $record->new_values ?? [];

        $decodeValue = function ($value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return $value;
        };

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

        $normalizeDocuments = function ($value) use ($decodeValue) {
            $value = $decodeValue($value);

            if (! is_array($value)) {
                return [];
            }

            $documents = [];

            foreach ($value as $document) {
                if (! is_array($document)) {
                    continue;
                }

                $documents[] = [
                    'file_name' => $document['file_name'] ?? '',
                    'file_path' => $document['file_path'] ?? '',
                    'file_type' => $document['file_type'] ?? '',
                    'file_size' => (int) ($document['file_size'] ?? 0),
                ];
            }

            usort($documents, fn ($a, $b) => ($a['file_path'] ?: $a['file_name']) <=> ($b['file_path'] ?: $b['file_name']));

            return $documents;
        };

        $formatValue = function ($key, $value) use ($decodeValue, $normalizeDocuments, $formatSize) {
            if ($value === null || $value === '') {
                return '-';
            }

            if ($key === 'documents') {
                $documents = $normalizeDocuments($value);

                if (count($documents) === 0) {
                    return '-';
                }

                return collect($documents)
                    ->map(function ($document, $index) use ($formatSize) {
                        $fileName = $document['file_name'] ?: '-';
                        $fileType = $document['file_type'] ?: '-';
                        $fileSize = $formatSize($document['file_size'] ?? 0);

                        return ($index + 1) . '. ' . $fileName . ' (' . $fileType . ', ' . $fileSize . ')';
                    })
                    ->implode("\n");
            }

            if ($key === 'document_types') {
                $types = $decodeValue($value);

                if (is_array($types)) {
                    return implode(', ', $types);
                }

                return (string) $value;
            }

            $decoded = $decodeValue($value);

            if (is_array($decoded)) {
                return collect($decoded)
                    ->map(fn ($item, $itemKey) => is_string($itemKey) ? $itemKey . ': ' . (is_scalar($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE)) : (is_scalar($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE)))
                    ->implode("\n");
            }

            if (is_bool($decoded)) {
                return $decoded ? 'Yes' : 'No';
            }

            return (string) $decoded;
        };

        $fieldLabel = function ($key) {
            return str($key)
                ->replace('_id', '')
                ->replace('_', ' ')
                ->title();
        };

        $keys = collect(array_keys($oldValues))
            ->merge(array_keys($newValues))
            ->unique()
            ->values();

        $hasDocumentTypes = array_key_exists('document_types', $oldValues) || array_key_exists('document_types', $newValues);
    @endphp

    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                Activity Log Details
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Complete activity information
            </p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Log Information
            </h3>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-sm font-semibold text-gray-500">Action</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->action }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Module</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->module }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Record</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->record_title ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">Date Time</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->created_at?->timezone('Asia/Karachi')->format('d M Y h:i A') }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">IP Address</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->ip_address ?: '-' }}</div>
                </div>
            </div>

            @if ($record->description)
                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-500">Description</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->description }}</div>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                User Information
            </h3>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-sm font-semibold text-gray-500">User Name</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->user_name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-gray-500">User Email</div>
                    <div class="text-base text-gray-950 dark:text-white">{{ $record->user_email ?: '-' }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Changes Detail
            </h3>

            @if ($keys->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                                <th class="px-3 py-2 font-semibold">Field</th>
                                <th class="px-3 py-2 font-semibold">Old Value</th>
                                <th class="px-3 py-2 font-semibold">New Value</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($keys as $key)
                                @php
                                    if ($key === 'updated_at') {
                                        continue;
                                    }

                                    if ($key === 'document_type' && $hasDocumentTypes) {
                                        continue;
                                    }

                                    $oldValue = $oldValues[$key] ?? null;
                                    $newValue = $newValues[$key] ?? null;

                                    if ($key === 'documents') {
                                        $oldDocs = $normalizeDocuments($oldValue);
                                        $newDocs = $normalizeDocuments($newValue);

                                        if (json_encode($oldDocs) === json_encode($newDocs)) {
                                            continue;
                                        }
                                    }

                                    $formattedOldValue = $formatValue($key, $oldValue);
                                    $formattedNewValue = $formatValue($key, $newValue);
                                @endphp

                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-3 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $fieldLabel($key) }}
                                    </td>

                                    <td class="whitespace-pre-line px-3 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $formattedOldValue }}
                                    </td>

                                    <td class="whitespace-pre-line px-3 py-3 text-gray-700 dark:text-gray-300">
                                        {{ $formattedNewValue }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Koi changes detail available nahi.
                </p>
            @endif
        </div>
    </div>
</x-filament-panels::page>