@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>My Documents</h1>
            <p>
                View and securely download documents assigned to your portal account.
            </p>
        </div>
    </div>

    <div class="cp-table-card">
        <div
            class="cp-table-wrap"
            role="region"
            aria-label="Customer documents"
            tabindex="0"
        >
            <table class="cp-table">
                <caption class="cp-sr-only">
                    Customer documents
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Document</th>
                        <th scope="col">Service</th>
                        <th scope="col">Type</th>
                        <th scope="col">Status</th>
                        <th scope="col">Expiry</th>
                        <th scope="col">Files</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>
                                <strong>
                                    {{ $document->title ?: $document->document_types_text }}
                                </strong>
                            </td>

                            <td>
                                {{ $document->service_type ?: '-' }}
                            </td>

                            <td>
                                {{ $document->document_types_text }}
                            </td>

                            <td>
                                <span class="cp-badge">
                                    {{ $document->status ?: '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $document->expiry_date?->format('d M Y') ?: '-' }}
                            </td>

                            <td>
                                @forelse ($document->documents_list as $index => $file)
                                    <div class="cp-file-action">
                                        <strong>
                                            {{ $file['file_name'] ?? ('File '.($index + 1)) }}
                                        </strong>

                                        <br>

                                        <a
                                            href="{{ route('customer.portal.documents.download', [
                                                'customerDocument' => $document,
                                                'fileIndex' => $index,
                                                'view' => 1,
                                            ]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            View
                                        </a>

                                        <span aria-hidden="true"> | </span>

                                        <a
                                            href="{{ route('customer.portal.documents.download', [
                                                'customerDocument' => $document,
                                                'fileIndex' => $index,
                                            ]) }}"
                                        >
                                            Download
                                        </a>
                                    </div>
                                @empty
                                    No file available.
                                @endforelse

                                @if ($document->documents_count > 1)
                                    <a
                                        href="{{ route('customer.portal.documents.download', [
                                            'customerDocument' => $document,
                                            'fileIndex' => 'all',
                                        ]) }}"
                                    >
                                        Download All
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                No documents found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include(
            'customer-portal.partials.pagination',
            [
                'paginator' =>
                    $documents,

                'label' =>
                    'Customer documents pagination',
            ]
        )
    </div>
@endsection