@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Support Tickets</h1>
            <p>
                Create and track your support requests.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="cp-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="cp-alert">
            <strong>Please correct the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cp-form-card">
        <form
            method="POST"
            action="{{ route('customer.portal.support.store') }}"
        >
            @csrf

            <div class="cp-form-grid">
                <div>
                    <label for="support-service">Service</label>

                    <select
                        id="support-service"
                        name="service_type"
                    >
                        <option value="">Select Service</option>

                        @foreach ([
                            'Umrah Package',
                            'Visit Visa',
                            'Air Ticketing',
                            'Hotel Booking',
                            'Travel Insurance',
                            'Pakistan Tours',
                        ] as $service)
                            <option
                                value="{{ $service }}"
                                @selected(old('service_type') === $service)
                            >
                                {{ $service }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="support-priority">Priority</label>

                    <select
                        id="support-priority"
                        name="priority"
                    >
                        @foreach ([
                            'Normal',
                            'High',
                            'Urgent',
                        ] as $priority)
                            <option
                                value="{{ $priority }}"
                                @selected(old('priority', 'Normal') === $priority)
                            >
                                {{ $priority }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="support-subject">Subject</label>

                    <input
                        id="support-subject"
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        maxlength="255"
                        required
                    >
                </div>
            </div>

            <div class="cp-field-block">
                <label for="support-message">Message</label>

                <textarea
                    id="support-message"
                    name="message"
                    rows="4"
                    maxlength="5000"
                    required
                >{{ old('message') }}</textarea>
            </div>

            <button type="submit">
                Create Ticket
            </button>
        </form>
    </div>

    <div class="cp-table-card">
        <div
            class="cp-table-wrap"
            role="region"
            aria-label="Support tickets"
            tabindex="0"
        >
            <table class="cp-table">
                <caption class="cp-sr-only">
                    Support tickets
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Ticket No</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Service</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Status</th>
                        <th scope="col">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('customer.portal.support.show', $ticket) }}">
                                    {{ $ticket->ticket_no }}
                                </a>
                            </td>

                            <td>{{ $ticket->subject }}</td>
                            <td>{{ $ticket->service_type ?: '-' }}</td>
                            <td>{{ $ticket->priority }}</td>

                            <td>
                                <span class="cp-badge">
                                    {{ $ticket->status }}
                                </span>
                            </td>

                            <td>
                                {{ $ticket->created_at->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                No support tickets found.
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
                    $tickets,

                'label' =>
                    'Support tickets pagination',
            ]
        )
    </div>
@endsection