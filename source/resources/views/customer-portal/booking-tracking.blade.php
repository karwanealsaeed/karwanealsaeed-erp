@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Booking Tracking</h1>
            <p>
                Track your visa, flight, Umrah and hotel bookings.
            </p>
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

            <div
                class="cp-table-wrap"
                role="region"
                aria-label="{{ $title }}"
                tabindex="0"
            >
                <table class="cp-table">
                    <caption class="cp-sr-only">
                        {{ $title }}
                    </caption>

                    <thead>
                        <tr>
                            <th scope="col">Reference</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>
                                    {{
                                        $record->booking_no
                                        ?? $record->application_no
                                        ?? $record->reference_no
                                        ?? ('#'.$record->id)
                                    }}
                                </td>

                                <td>
                                    <span class="cp-badge">
                                        {{ $record->status ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{
                                        isset($record->created_at)
                                            ? \Carbon\Carbon::parse($record->created_at)->format('d M Y')
                                            : '-'
                                    }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    No records found.
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
                        $records,

                    'label' =>
                        $title.' pagination',
                ]
            )
        </div>
    @endforeach
@endsection