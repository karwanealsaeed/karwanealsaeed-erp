@extends('customer-portal.layout')

@section('content')
    <div class="cp-doc-grid">
        @forelse ($documents as $document)
            <div class="cp-doc-card">
                <h3>{{ $document->title ?: $document->document_types_text }}</h3>
                <p><strong>Service:</strong> {{ $document->service_type ?: '-' }}</p>
                <p><strong>Type:</strong> {{ $document->document_types_text }}</p>
                <p><strong>Status:</strong> <span class="cp-badge">{{ $document->status ?: '-' }}</span></p>
                <p><strong>Files:</strong> {{ $document->documents_count }}</p>
            </div>
        @empty
            <div class="cp-card">
                No documents found.
            </div>
        @endforelse
    </div>
@endsection