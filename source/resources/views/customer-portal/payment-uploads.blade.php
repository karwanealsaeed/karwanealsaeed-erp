@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Payment Upload</h1>
            <p>Upload payment proof for admin review.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-form-card">
        <form method="POST" action="{{ route('customer.portal.payment_uploads.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="cp-form-grid">
                <select name="service_type" required>
                    <option value="">Select Service</option>
                    <option value="Visa">Visa</option>
                    <option value="Flight">Flight</option>
                    <option value="Umrah">Umrah</option>
                    <option value="Hotel">Hotel</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Pakistan Tours">Pakistan Tours</option>
                </select>

                <input type="text" name="reference_no" placeholder="Invoice / Booking Reference optional">
                <input type="number" step="0.01" name="amount" placeholder="Amount" required>

                <select name="payment_method" required>
                    <option value="">Payment Method</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash Deposit">Cash Deposit</option>
                    <option value="JazzCash">JazzCash</option>
                    <option value="EasyPaisa">EasyPaisa</option>
                    <option value="Cheque">Cheque</option>
                </select>

                <input type="date" name="payment_date" required>
                <input type="text" name="transaction_id" placeholder="Transaction ID optional">
                <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf,.webp">
            </div>

            <textarea name="customer_notes" rows="3" placeholder="Notes optional"></textarea>

            <button type="submit">Upload Payment Proof</button>
        </form>
    </div>

    <div class="cp-table-card">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Upload No</th>
                    <th>Service</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($uploads as $upload)
                    <tr>
                        <td>{{ $upload->upload_no }}</td>
                        <td>{{ $upload->service_type }}</td>
                        <td>{{ $upload->reference_no ?: '-' }}</td>
                        <td>PKR {{ number_format((float) $upload->amount, 2) }}</td>
                        <td>{{ $upload->payment_method }}</td>
                        <td>{{ $upload->payment_date?->format('d M Y') }}</td>
                        <td>{{ $upload->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No payment uploads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection