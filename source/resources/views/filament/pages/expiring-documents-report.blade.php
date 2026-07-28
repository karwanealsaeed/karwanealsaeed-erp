<x-filament-panels::page>
    <x-report-actions />

    @php
        $rows = $this->getRows();

        $expiredCount = $rows->filter(fn ($row) => $row->expiry_date && $row->expiry_date->isPast())->count();
        $activeCount = $rows->count() - $expiredCount;
    @endphp

    <div class="kes-expiring-documents-report space-y-6">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Total Documents</div>
                <div class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $rows->count() }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Expiring Soon</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600">{{ $activeCount }}</div>
            </div>

            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <div class="text-sm font-semibold text-gray-500">Expired</div>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ $expiredCount }}</div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-bold text-gray-950 dark:text-white">
                Expiring Documents
            </h3>

            @if ($rows->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left dark:border-gray-700">
                                <th class="px-3 py-2 font-semibold">Customer</th>
                                <th class="px-3 py-2 font-semibold">Mobile</th>
                                <th class="px-3 py-2 font-semibold">Document Types</th>
                                <th class="px-3 py-2 font-semibold">Service</th>
                                <th class="px-3 py-2 font-semibold">Expiry Date</th>
                                <th class="px-3 py-2 font-semibold">Status</th>
                                <th class="px-3 py-2 font-semibold">Files</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($rows as $row)
                                @php
                                    $isExpired = $row->expiry_date && $row->expiry_date->isPast();
                                    $daysLeft = $row->expiry_date ? now()->startOfDay()->diffInDays($row->expiry_date->startOfDay(), false) : null;
                                @endphp

                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-3 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $row->customer?->name ?? '-' }}
                                    </td>

                                    <td class="px-3 py-3">
                                        {{ $row->customer?->mobile ?? '-' }}
                                    </td>

                                    <td class="px-3 py-3">
                                        {{ $row->document_types_text }}
                                    </td>

                                    <td class="px-3 py-3">
                                        {{ $row->service_type ?: '-' }}
                                    </td>

                                    <td class="px-3 py-3">
                                        {{ $row->expiry_date ? $row->expiry_date->format('d M Y') : '-' }}
                                    </td>

                                    <td class="px-3 py-3">
                                        @if ($isExpired)
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                Expired
                                            </span>
                                        @else
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">
                                                {{ $daysLeft }} Days Left
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-3 py-3">
                                        {{ $row->documents_count }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Koi expiring document nahi mila.
                </p>
            @endif
        </div>
    </div>

    {{-- PHASE 16F-3D13 EXPIRING DOCUMENTS RESPONSIVE CONTAINMENT START --}}
    <style>
        .kes-expiring-documents-report {
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            overflow-x: clip !important;
        }

        .kes-expiring-documents-report > *,
        .kes-expiring-documents-report > * > *,
        .kes-expiring-documents-report .overflow-x-auto {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .kes-expiring-documents-report .overflow-x-auto {
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            overscroll-behavior-inline: contain;
        }

        .kes-expiring-documents-report table {
            width: 100% !important;
            min-width: 760px !important;
        }

        .kes-expiring-documents-report th,
        .kes-expiring-documents-report td {
            overflow-wrap: anywhere !important;
        }

        @media (max-width: 760px) {
            .kes-expiring-documents-report {
                gap: .8rem !important;
            }

            .kes-expiring-documents-report table {
                min-width: 700px !important;
                font-size: .72rem !important;
            }
        }
    </style>
    {{-- PHASE 16F-3D13 EXPIRING DOCUMENTS RESPONSIVE CONTAINMENT END --}}
</x-filament-panels::page>