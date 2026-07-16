@extends('customer-portal.layout')

@section('content')
    <div class="cp-card">
        <h2 style="margin-top:0;">My Profile</h2>

        <div class="cp-table-wrap">
            <table class="cp-table">
                <tbody>
                    <tr><th>Name</th><td>{{ $customer->name ?? '-' }}</td></tr>
                    <tr><th>Father Name</th><td>{{ $customer->father_name ?? '-' }}</td></tr>
                    <tr><th>CNIC</th><td>{{ $customer->cnic ?? '-' }}</td></tr>
                    <tr><th>Passport No</th><td>{{ $customer->passport_no ?? '-' }}</td></tr>
                    <tr><th>Mobile</th><td>{{ $customer->mobile ?? '-' }}</td></tr>
                    <tr><th>WhatsApp</th><td>{{ $customer->whatsapp ?? '-' }}</td></tr>
                    <tr><th>Email</th><td>{{ $customer->email ?? '-' }}</td></tr>
                    <tr><th>Address</th><td>{{ $customer->address ?? '-' }}</td></tr>
                    <tr><th>City</th><td>{{ $customer->city ?? '-' }}</td></tr>
                    <tr><th>Country</th><td>{{ $customer->country ?? '-' }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection