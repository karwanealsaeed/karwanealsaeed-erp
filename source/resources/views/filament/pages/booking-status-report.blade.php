<x-filament-panels::page>
    @php
        $data = $this->getReportData();

        $sections = [
            [
                'title' => 'Visa Status',
                'total' => $data['visa_total'],
                'rows' => $data['visa_rows'],
            ],
            [
                'title' => 'Flight Status',
                'total' => $data['flight_total'],
                'rows' => $data['flight_rows'],
            ],
            [
                'title' => 'Umrah Status',
                'total' => $data['umrah_total'],
                'rows' => $data['umrah_rows'],
            ],
            [
                'title' => 'Hotel Status',
                'total' => $data['hotel_total'],
                'rows' => $data['hotel_rows'],
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-report-actions />
        <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                Booking Status Report
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                Visa, Flight, Umrah aur Hotel bookings ka status wise summary.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Visa Bookings
                </p>

                <h3 class="text-2xl font-bold text-blue-600">
                    {{ number_format($data['visa_total']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Flight Bookings
                </p>

                <h3 class="text-2xl font-bold text-green-600">
                    {{ number_format($data['flight_total']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Umrah Bookings
                </p>

                <h3 class="text-2xl font-bold text-purple-600">
                    {{ number_format($data['umrah_total']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Hotel Bookings
                </p>

                <h3 class="text-2xl font-bold text-orange-600">
                    {{ number_format($data['hotel_total']) }}
                </h3>
            </div>

            <div class="rounded-xl bg-white p-6 shadow dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Bookings
                </p>

                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($data['grand_total']) }}
                </h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            @foreach ($sections as $section)
                <div class="overflow-hidden rounded-xl bg-white shadow dark:bg-gray-900">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $section['title'] }}
                            </h3>

                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                Total: {{ number_format($section['total']) }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        Status
                                    </th>

                                    <th class="px-6 py-3 text-right">
                                        Count
                                    </th>

                                    <th class="px-6 py-3 text-right">
                                        Percentage
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($section['rows'] as $row)
                                    <tr class="border-b border-gray-200 dark:border-gray-800">
                                        <td class="px-6 py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ $row['status'] }}
                                        </td>

                                        <td class="px-6 py-3 text-right">
                                            {{ number_format($row['count']) }}
                                        </td>

                                        <td class="px-6 py-3 text-right">
                                            {{ number_format($row['percentage'], 2) }}%
                                        </td>
                                    </tr>
                                @endforeach

                                <tr class="bg-gray-50 font-bold dark:bg-gray-800">
                                    <td class="px-6 py-3">
                                        Total
                                    </td>

                                    <td class="px-6 py-3 text-right">
                                        {{ number_format($section['total']) }}
                                    </td>

                                    <td class="px-6 py-3 text-right">
                                        {{ $section['total'] > 0 ? '100.00%' : '0.00%' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
