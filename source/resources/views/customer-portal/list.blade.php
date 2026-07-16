@extends('customer-portal.layout')

@section('content')
    <div class="cp-card">
        <h2 style="margin-top:0;">{{ $heading }}</h2>

        <div class="cp-table-wrap">
            <table class="cp-table">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection