@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>{{ $ticket->ticket_no }}</h1>
            <p>{{ $ticket->subject }} • {{ $ticket->status }}</p>
        </div>
        <a href="{{ route('customer.portal.support') }}">Back</a>
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-card-grid">
        @foreach ($ticket->replies as $reply)
            <div class="cp-info-card {{ $reply->is_admin ? 'admin-reply' : '' }}">
                <span>{{ $reply->is_admin ? 'Karwan Support' : 'You' }} • {{ $reply->created_at->format('d M Y h:i A') }}</span>
                <p>{{ $reply->message }}</p>
            </div>
        @endforeach
    </div>

    <div class="cp-form-card">
        <form method="POST" action="{{ route('customer.portal.support.reply', $ticket) }}">
            @csrf
            <textarea name="message" rows="4" placeholder="Write reply..." required></textarea>
            <button type="submit">Send Reply</button>
        </form>
    </div>
@endsection