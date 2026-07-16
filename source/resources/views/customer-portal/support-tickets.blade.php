@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Support Tickets</h1>
            <p>Create and track your support requests.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-form-card">
        <form method="POST" action="{{ route('customer.portal.support.store') }}">
            @csrf

            <div class="cp-form-grid">
                <select name="service_type">
                    <option value="">Select Service</option>
                    <option value="Umrah Package">Umrah Package</option>
                    <option value="Visit Visa">Visit Visa</option>
                    <option value="Air Ticketing">Air Ticketing</option>
                    <option value="Hotel Booking">Hotel Booking</option>
                    <option value="Travel Insurance">Travel Insurance</option>
                    <option value="Pakistan Tours">Pakistan Tours</option>
                </select>

                <select name="priority">
                    <option value="Normal">Normal</option>
                    <option value="High">High</option>
                    <option value="Urgent">Urgent</option>
                </select>

                <input type="text" name="subject" placeholder="Subject" required>
            </div>

            <textarea name="message" rows="4" placeholder="Write your issue..." required></textarea>

            <button type="submit">Create Ticket</button>
        </form>
    </div>

    <div class="cp-table-card">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Ticket No</th>
                    <th>Subject</th>
                    <th>Service</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>
                            <a href="{{ route('customer.portal.support.show', $ticket) }}">{{ $ticket->ticket_no }}</a>
                        </td>
                        <td>{{ $ticket->subject }}</td>
                        <td>{{ $ticket->service_type ?: '-' }}</td>
                        <td>{{ $ticket->priority }}</td>
                        <td>{{ $ticket->status }}</td>
                        <td>{{ $ticket->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No support tickets found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection