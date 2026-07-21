@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>My Documents</h1>
            <p>View and securely download documents assigned to your portal account.</p>
        </div>
    </div>

    <div class="cp-table-card">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Service</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Expiry</th>
                    <th>Files</th>
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
                                <div style="margin-bottom: 8px;">
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
                                        rel="noopener"
                                    >
                                        View
                                    </a>

                                    &nbsp;|&nbsp;

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
@endsection