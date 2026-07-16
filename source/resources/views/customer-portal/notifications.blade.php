@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Notifications & Offers</h1>
            <p>Latest updates, alerts and promotions. Unread: {{ $unreadCount ?? 0 }}</p>
        </div>

        @if (($notifications->count() ?? 0) > 0)
            <form method="POST" action="{{ route('customer.portal.notifications.mark_all_read') }}">
                @csrf
                <button type="submit">Mark All Read</button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-card-grid">
        @forelse ($notifications as $notification)
            @php
                $isRead = in_array((int) $notification->id, $readNotificationIds ?? [], true);
            @endphp

            <div class="cp-info-card cp-notification-card {{ $isRead ? 'read' : 'unread' }}">
                <div class="cp-notification-top">
                    <span>
                        {{ $notification->type }}
                        {{ $notification->service_type ? '• ' . $notification->service_type : '' }}
                    </span>

                    <strong>{{ $isRead ? 'Read' : 'Unread' }}</strong>
                </div>

                <h3>{{ $notification->title }}</h3>
                <p>{{ $notification->message }}</p>

                <div class="cp-notification-actions">
                    @if (! $isRead)
                        <form method="POST" action="{{ route('customer.portal.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit">Mark Read</button>
                        </form>
                    @endif

                    @if ($notification->action_url)
                        <a href="{{ $notification->action_url }}" target="_blank">
                            {{ $notification->action_label ?: 'Open' }}
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="cp-info-card">
                <h3>No notifications</h3>
                <p>Abhi koi new notification ya offer available nahi hai.</p>
            </div>
        @endforelse
    </div>
@endsection