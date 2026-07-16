@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Booking Tracking</h1>
            <p>Track all your visa, flight, Umrah and hotel bookings.</p>
        </div>
    </div>

    @foreach ([
        'Visa Applications' => $visaApplications,
        'Flight Bookings' => $flightBookings,
        'Umrah Bookings' => $umrahBookings,
        'Hotel Bookings' => $hotelBookings,
    ] as $title => $records)
        <div class="cp-table-card">
            <h2>{{ $title }}</h2>

            <table class="cp-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->booking_no ?? $record->application_no ?? $record->reference_no ?? ('#' . $record->id) }}</td>
                            <td>{{ $record->status ?? '-' }}</td>
                            <td>{{ isset($record->created_at) ? \Carbon\Carbon::parse($record->created_at)->format('d M Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <br>
    @endforeach
@endsection