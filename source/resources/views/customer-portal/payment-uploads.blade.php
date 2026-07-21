@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Payment Upload</h1>
            <p>
                Upload payment proof securely for office review.
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
            action="{{ route('customer.portal.payment_uploads.store') }}"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="cp-form-grid">
                <div>
                    <label for="payment-service">Service</label>

                    <select
                        id="payment-service"
                        name="service_type"
                        required
                    >
                        <option value="">Select Service</option>

                        @foreach ([
                            'Visa',
                            'Flight',
                            'Umrah',
                            'Hotel',
                            'Insurance',
                            'Pakistan Tours',
                            'Other',
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
                    <label for="payment-reference">
                        Invoice / Booking Reference
                    </label>

                    <input
                        id="payment-reference"
                        type="text"
                        name="reference_no"
                        value="{{ old('reference_no') }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="payment-amount">Amount</label>

                    <input
                        id="payment-amount"
                        type="number"
                        name="amount"
                        value="{{ old('amount') }}"
                        min="1"
                        max="100000000"
                        step="0.01"
                        inputmode="decimal"
                        required
                    >
                </div>

                <div>
                    <label for="payment-method">Payment Method</label>

                    <select
                        id="payment-method"
                        name="payment_method"
                        required
                    >
                        <option value="">Select Payment Method</option>

                        @foreach ([
                            'Bank Transfer',
                            'Cash Deposit',
                            'JazzCash',
                            'EasyPaisa',
                            'Cheque',
                            'Credit Card',
                            'Other',
                        ] as $method)
                            <option
                                value="{{ $method }}"
                                @selected(old('payment_method') === $method)
                            >
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment-date">Payment Date</label>

                    <input
                        id="payment-date"
                        type="date"
                        name="payment_date"
                        value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                        max="{{ now()->format('Y-m-d') }}"
                        required
                    >
                </div>

                <div>
                    <label for="payment-transaction">Transaction ID</label>

                    <input
                        id="payment-transaction"
                        type="text"
                        name="transaction_id"
                        value="{{ old('transaction_id') }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="payment-receipt">Payment Receipt</label>

                    <input
                        id="payment-receipt"
                        type="file"
                        name="receipt_file"
                        accept=".jpg,.jpeg,.png,.pdf,.webp"
                        required
                    >

                    <small>
                        Private upload. JPG, PNG, WebP or PDF; maximum 5 MB.
                    </small>
                </div>
            </div>

            <div class="cp-field-block">
                <label for="payment-notes">Notes</label>

                <textarea
                    id="payment-notes"
                    name="customer_notes"
                    rows="3"
                    maxlength="3000"
                >{{ old('customer_notes') }}</textarea>
            </div>

            <button type="submit">
                Upload Payment Proof
            </button>
        </form>
    </div>

    <div class="cp-table-card">
        <div
            class="cp-table-wrap"
            role="region"
            aria-label="Payment uploads"
            tabindex="0"
        >
            <table class="cp-table">
                <caption class="cp-sr-only">
                    Payment uploads
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Upload No</th>
                        <th scope="col">Service</th>
                        <th scope="col">Reference</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Method</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Receipt</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($uploads as $upload)
                        <tr>
                            <td>{{ $upload->upload_no }}</td>
                            <td>{{ $upload->service_type }}</td>
                            <td>{{ $upload->reference_no ?: '-' }}</td>

                            <td>
                                PKR {{ number_format((float) $upload->amount, 2) }}
                            </td>

                            <td>{{ $upload->payment_method }}</td>

                            <td>
                                {{ $upload->payment_date?->format('d M Y') ?: '-' }}
                            </td>

                            <td>
                                <span class="cp-badge">
                                    {{ $upload->status }}
                                </span>

                                @if (
                                    $upload->status === 'Rejected'
                                    && filled($upload->rejection_reason)
                                )
                                    <br>

                                    <small>
                                        {{ $upload->rejection_reason }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                @if (filled($upload->receipt_file))
                                    <a
                                        href="{{ route('customer.portal.payment_uploads.receipt', [
                                            'customerPaymentUpload' => $upload,
                                            'view' => 1,
                                        ]) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        View
                                    </a>

                                    <span aria-hidden="true"> | </span>

                                    <a
                                        href="{{ route('customer.portal.payment_uploads.receipt', [
                                            'customerPaymentUpload' => $upload,
                                        ]) }}"
                                    >
                                        Download
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                No payment uploads found.
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
                    $uploads,

                'label' =>
                    'Payment uploads pagination',
            ]
        )
    </div>
@endsection