@extends('customer-portal.layout')

@section('content')
    <div class="cp-page-head">
        <div>
            <h1>{{ $heading }}</h1>
            <p>
                Only records linked with your portal account are displayed.
            </p>
        </div>
    </div>

    <div class="cp-table-card">
        <div
            class="cp-table-wrap"
            role="region"
            aria-label="{{ $heading }}"
            tabindex="0"
        >
            <table class="cp-table">
                <caption class="cp-sr-only">
                    {{ $heading }}
                </caption>

                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th scope="col">
                                {{ $column['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            @foreach ($columns as $column)
                                @php
                                    $value = data_get($record, $column['key']);
                                    $type = $column['type'] ?? 'text';
                                @endphp

                                <td>
                                    @if ($type === 'money')
                                        PKR {{ number_format((float) ($value ?? 0), 2) }}
                                    @elseif ($type === 'date')
                                        {{ filled($value) ? \Carbon\Carbon::parse($value)->format('d M Y') : '-' }}
                                    @elseif ($type === 'status')
                                        <span class="cp-badge">
                                            {{ filled($value) ? $value : '-' }}
                                        </span>
                                    @else
                                        {{ filled($value) ? $value : '-' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">
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
                    $heading.' pagination',
            ]
        )
    </div>
@endsection