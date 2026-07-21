@extends('customer-portal.layout')

@section('title', 'Visa Applications')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Visa Applications</h1>

            <p>
                Apni Visa applications, travel dates aur Visa validity details dekhein.
            </p>
        </div>
    </div>

    <section class="cp-table-card">
        <div class="cp-table-wrap">
            <table class="cp-table">
                <thead>
                    <tr>
                        <th>Application</th>
                        <th>Country / Visa Type</th>
                        <th>Passport</th>
                        <th>Travel</th>
                        <th>Visa Validity</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($visaApplications as $application)
                        @php
                            $daysRemaining =
                                $application->visa_expiry_date
                                    ? (int) now('Asia/Karachi')
                                        ->startOfDay()
                                        ->diffInDays(
                                            $application
                                                ->visa_expiry_date
                                                ->copy()
                                                ->startOfDay(),
                                            false,
                                        )
                                    : null;

                            $expiryLabel =
                                match (true) {
                                    $daysRemaining === null =>
                                        'Expiry Missing',

                                    $daysRemaining < 0 =>
                                        'Expired',

                                    $daysRemaining <= 7 =>
                                        'Expiring Soon',

                                    $daysRemaining <= 30 =>
                                        'Expiry Warning',

                                    default =>
                                        'Valid',
                                };

                            $expiryClass =
                                match ($expiryLabel) {
                                    'Expired',
                                    'Expiry Missing',
                                    'Expiring Soon' =>
                                        'danger',

                                    'Expiry Warning' =>
                                        'warning',

                                    default =>
                                        'success',
                                };
                        @endphp

                        <tr>
                            <td data-label="Application">
                                <strong>
                                    {{ $application->application_no }}
                                </strong>

                                <div class="cp-muted">
                                    Created:
                                    {{ $application->created_at?->format('d M Y') ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Country / Visa Type">
                                <strong>
                                    {{ $application->country?->name ?: '-' }}
                                </strong>

                                <div class="cp-muted">
                                    {{ $application->visaType?->name ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Passport">
                                {{ $application->passport_no ?: '-' }}

                                <div class="cp-muted">
                                    Passport expiry:
                                    {{ $application->passport_expiry_date?->format('d M Y') ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Travel">
                                {{ $application->travel_date?->format('d M Y') ?: '-' }}

                                <div class="cp-muted">
                                    Return:
                                    {{ $application->return_date?->format('d M Y') ?: '-' }}
                                </div>
                            </td>

                            <td data-label="Visa Validity">
                                <div>
                                    Issued:
                                    {{ $application->visa_issue_date?->format('d M Y') ?: '-' }}
                                </div>

                                <div>
                                    Expires:
                                    {{ $application->visa_expiry_date?->format('d M Y') ?: '-' }}
                                </div>

                                <span class="cp-status cp-status-{{ $expiryClass }}">
                                    {{ $expiryLabel }}
                                </span>

                                @if ($daysRemaining !== null && $daysRemaining >= 0)
                                    <div class="cp-muted">
                                        {{ $daysRemaining }} day(s) remaining
                                    </div>
                                @endif
                            </td>

                            <td data-label="Status">
                                <span class="cp-status">
                                    {{ $application->status }}
                                </span>

                                <div class="cp-muted">
                                    Passport:
                                    {{ $application->passport_status }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="cp-empty"
                            >
                                Abhi koi Visa application available nahi hai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="cp-pagination">
        {{ $visaApplications->links() }}
    </div>
@endsection
