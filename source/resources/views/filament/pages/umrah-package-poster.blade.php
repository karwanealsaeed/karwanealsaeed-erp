<x-filament-panels::page>
    @php
        $packages = $this->getPackages();
        $airlines = $this->getAirlines();
        $routes = $this->getRoutes();
    @endphp

    <div class="kes-poster-admin">
        <div class="kes-poster-admin-header">
            <div>
                <h1>Umrah Package Poster Generator</h1>
                <p>Filter packages, select required packages and generate printable poster.</p>
            </div>

            <a href="{{ route('website.umrah') }}" target="_blank" class="kes-poster-admin-link">
                View Public Page
            </a>
        </div>

        <div class="kes-poster-filter-card">
            <form method="GET" action="{{ url()->current() }}">
                <div class="kes-poster-filter-grid">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search package / hotel / route / airline"
                    >

                    <select name="airline">
                        <option value="">Filter by Airline</option>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline }}" @selected(request('airline') === $airline)>
                                {{ $airline }}
                            </option>
                        @endforeach
                    </select>

                    <select name="route">
                        <option value="">Filter by Route</option>
                        @foreach ($routes as $route)
                            <option value="{{ $route }}" @selected(request('route') === $route)>
                                {{ $route }}
                            </option>
                        @endforeach
                    </select>

                    <select name="duration">
                        <option value="">Filter by Duration</option>
                        <option value="1-10" @selected(request('duration') === '1-10')>1 - 10 Nights</option>
                        <option value="11-20" @selected(request('duration') === '11-20')>11 - 20 Nights</option>
                        <option value="21-30" @selected(request('duration') === '21-30')>21 - 30 Nights</option>
                        <option value="31+" @selected(request('duration') === '31+')>31+ Nights</option>
                    </select>
                </div>

                <div class="kes-poster-filter-actions">
                    <button type="submit">Apply Filters</button>
                    <a href="{{ url()->current() }}">Reset</a>
                </div>
            </form>
        </div>

        <form method="GET" action="{{ route('umrah-package-posters.print') }}" target="_blank">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="airline" value="{{ request('airline') }}">
            <input type="hidden" name="route" value="{{ request('route') }}">
            <input type="hidden" name="duration" value="{{ request('duration') }}">

            <div class="kes-poster-selected-bar">
                <div>
                    <strong>Showing {{ $packages->count() }} package(s)</strong>
                    <span>Select packages for poster.</span>
                </div>

                <div class="kes-poster-selected-actions">
                    <button type="button" onclick="document.querySelectorAll('.kes-package-check').forEach(el => el.checked = true)">
                        Select All
                    </button>

                    <button type="button" onclick="document.querySelectorAll('.kes-package-check').forEach(el => el.checked = false)">
                        Clear
                    </button>

                    <button type="submit" class="primary">
                        Generate Poster
                    </button>
                </div>
            </div>

            @if ($packages->count() > 0)
                <div class="kes-poster-package-grid">
                    @foreach ($packages as $package)
                        @php
                            $group = $package->umrahGroupFlight;
                            $routeText = $package->flight_route ?: ($group?->route ?: '-');
                            $departureDate = $group?->departure_date ? $group->departure_date->format('d M Y') : '-';
                            $returnDate = $group?->return_date ? $group->return_date->format('d M Y') : '-';
                        @endphp

                        <label class="kes-poster-package-card">
                            <input
                                type="checkbox"
                                class="kes-package-check"
                                name="package_ids[]"
                                value="{{ $package->id }}"
                                checked
                            >

                            <div class="kes-poster-package-top">
                                <div>
                                    <h3>{{ $package->title }}</h3>
                                    <p>{{ $package->package_no }} | {{ $package->group_code ?: '-' }}</p>
                                </div>

                                <span>{{ $package->duration_nights ?: '-' }}N</span>
                            </div>

                            <div class="kes-poster-package-info">
                                <div><strong>Airline:</strong> {{ $package->airline ?: '-' }}</div>
                                <div><strong>Route:</strong> {{ $routeText }}</div>
                                <div><strong>Departure:</strong> {{ $departureDate }}</div>
                                <div><strong>Return:</strong> {{ $returnDate }}</div>
                                <div><strong>Makkah:</strong> {{ $package->makkah_hotel ?: '-' }} / {{ $package->makkah_nights ?: '-' }}N</div>
                                <div><strong>Madinah:</strong> {{ $package->madina_hotel ?: '-' }} / {{ $package->madina_nights ?: '-' }}N</div>
                            </div>

                            <div class="kes-poster-package-prices">
                                <span>Sharing {{ $package->sharing_price ? number_format((float) $package->sharing_price, 0) : 'N/A' }}</span>
                                <span>Quad {{ $package->quad_price ? number_format((float) $package->quad_price, 0) : 'N/A' }}</span>
                                <span>Triple {{ $package->triple_price ? number_format((float) $package->triple_price, 0) : 'N/A' }}</span>
                                <span>Double {{ $package->double_price ? number_format((float) $package->double_price, 0) : 'N/A' }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="kes-poster-empty">
                    No active package found for this filter.
                </div>
            @endif
        </form>
    </div>
</x-filament-panels::page>