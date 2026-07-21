@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>Family Members</h1>
            <p>
                Add family members for Umrah, visa and tours.
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
            action="{{ route('customer.portal.family.store') }}"
        >
            @csrf

            <div class="cp-form-grid">
                <div>
                    <label for="family-name">Full Name</label>

                    <input
                        id="family-name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >
                </div>

                <div>
                    <label for="family-relationship">Relationship</label>

                    <input
                        id="family-relationship"
                        type="text"
                        name="relationship"
                        value="{{ old('relationship') }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="family-cnic">CNIC</label>

                    <input
                        id="family-cnic"
                        type="text"
                        name="cnic"
                        value="{{ old('cnic') }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="family-passport">Passport No</label>

                    <input
                        id="family-passport"
                        type="text"
                        name="passport_no"
                        value="{{ old('passport_no') }}"
                        maxlength="255"
                    >
                </div>

                <div>
                    <label for="family-date-of-birth">Date of Birth</label>

                    <input
                        id="family-date-of-birth"
                        type="date"
                        name="date_of_birth"
                        value="{{ old('date_of_birth') }}"
                    >
                </div>

                <div>
                    <label for="family-mobile">Mobile</label>

                    <input
                        id="family-mobile"
                        type="text"
                        name="mobile"
                        value="{{ old('mobile') }}"
                        maxlength="255"
                        autocomplete="tel"
                    >
                </div>
            </div>

            <div class="cp-field-block">
                <label for="family-notes">Notes</label>

                <textarea
                    id="family-notes"
                    name="notes"
                    rows="3"
                    maxlength="3000"
                >{{ old('notes') }}</textarea>
            </div>

            <button type="submit">
                Add Family Member
            </button>
        </form>
    </div>

    <div class="cp-table-card">
        <div
            class="cp-table-wrap"
            role="region"
            aria-label="Family members"
            tabindex="0"
        >
            <table class="cp-table">
                <caption class="cp-sr-only">
                    Family members
                </caption>

                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Relationship</th>
                        <th scope="col">CNIC</th>
                        <th scope="col">Passport</th>
                        <th scope="col">DOB</th>
                        <th scope="col">Mobile</th>
                        <th scope="col">Action</th>
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
                                <form
                                    method="POST"
                                    action="{{ route('customer.portal.family.delete', $member) }}"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Delete this family member?')"
                                        aria-label="Delete {{ $member->name }}"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                No family members found.
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
                    $members,

                'label' =>
                    'Family members pagination',
            ]
        )
    </div>
@endsection