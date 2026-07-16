@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Family Members</h1>
            <p>Add family members for Umrah, visa and tours.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="cp-alert success">{{ session('success') }}</div>
    @endif

    <div class="cp-form-card">
        <form method="POST" action="{{ route('customer.portal.family.store') }}">
            @csrf

            <div class="cp-form-grid">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="text" name="relationship" placeholder="Relationship">
                <input type="text" name="cnic" placeholder="CNIC">
                <input type="text" name="passport_no" placeholder="Passport No">
                <input type="date" name="date_of_birth">
                <input type="text" name="mobile" placeholder="Mobile">
            </div>

            <textarea name="notes" rows="3" placeholder="Notes optional"></textarea>

            <button type="submit">Add Family Member</button>
        </form>
    </div>

    <div class="cp-table-card">
        <table class="cp-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>CNIC</th>
                    <th>Passport</th>
                    <th>DOB</th>
                    <th>Mobile</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->relationship ?: '-' }}</td>
                        <td>{{ $member->cnic ?: '-' }}</td>
                        <td>{{ $member->passport_no ?: '-' }}</td>
                        <td>{{ $member->date_of_birth?->format('d M Y') ?: '-' }}</td>
                        <td>{{ $member->mobile ?: '-' }}</td>
                        <td>
                            <form method="POST" action="{{ route('customer.portal.family.delete', $member) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Delete this family member?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No family members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection