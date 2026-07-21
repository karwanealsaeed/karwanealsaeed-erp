@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Notifications & Offers</h1>
            <p>
                Latest updates and alerts. Unread: {{ $unreadCount ?? 0 }}
            </p>
        </div>

        @if ($notifications->total() > 0)
            <form
                method="POST"
                action="{{ route('customer.portal.notifications.mark_all_read') }}"
            >
                @csrf

                <button type="submit">
                    Mark All Read
                </button>
            </form>
        @endif
    </div>

    @if (session('success'))
        <div class="cp-alert success">
            {{ session('success') }}
        </div>
    @endif

    <div class="cp-card-grid">
        @forelse ($notifications as $notification)
            @php
                $isRead = in_array(
                    (int) $notification->id,
                    $readNotificationIds ?? [],
                    true,
                );

                $actionUrl = trim(
                    (string) ($notification->action_url ?? ''),
                );

                $actionScheme = strtolower(
                    (string) parse_url(
                        $actionUrl,
                        PHP_URL_SCHEME,
                    ),
                );

                $isSafeRelativeUrl =
                    str_starts_with($actionUrl, '/')
                    && ! str_starts_with($actionUrl, '//');

                $isSafeAbsoluteUrl =
                    in_array(
                        $actionScheme,
                        [
                            'http',
                            'https',
                        ],
                        true,
                    );

                $hasSafeActionUrl =
                    $actionUrl !== ''
                    && (
                        $isSafeRelativeUrl
                        || $isSafeAbsoluteUrl
                    );
            @endphp

            <article class="cp-info-card cp-notification-card {{ $isRead ? 'read' : 'unread' }}">
                <div class="cp-notification-top">
                    <span>
                        {{ $notification->type }}

                        {{
                            $notification->service_type
                                ? '• '.$notification->service_type
                                : ''
                        }}
                    </span>

                    <strong>
                        {{ $isRead ? 'Read' : 'Unread' }}
                    </strong>
                </div>

                <h2>
                    {{ $notification->title }}
                </h2>

                <p>
                    {{ $notification->message }}
                </p>

                <div class="cp-notification-actions">
                    @if (! $isRead)
                        <form
                            method="POST"
                            action="{{ route('customer.portal.notifications.read', $notification) }}"
                        >
                            @csrf

                            <button type="submit">
                                Mark Read
                            </button>
                        </form>
                    @endif

                    @if ($hasSafeActionUrl)
                        <a
                            href="{{ $actionUrl }}"
                            @if ($isSafeAbsoluteUrl)
                                target="_blank"
                                rel="noopener noreferrer"
                            @endif
                        >
                            {{ $notification->action_label ?: 'Open' }}
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="cp-info-card">
                <h2>No notifications</h2>

                <p>
                    Abhi koi new notification ya offer available nahi hai.
                </p>
            </div>
        @endforelse
    </div>

    @include(
        'customer-portal.partials.pagination',
        [
            'paginator' =>
                $notifications,

            'label' =>
                'Notifications pagination',
        ]
    )
@endsection