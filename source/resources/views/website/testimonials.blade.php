@extends('website.layout')

@section('content')
    <main class="kes-testimonials-page">
        <section class="kes-testimonials-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-testimonials-hero-inner">
                <div class="kes-testimonials-hero-copy">
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('testimonials.home') }}
                        </a>

                        <span>/</span>

                        <span>{{ __('testimonials.title') }}</span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('testimonials.eyebrow') }}
                    </span>

                    <h1>{{ __('testimonials.title') }}</h1>

                    <p>{{ __('testimonials.subtitle') }}</p>
                </div>

                <div class="kes-testimonials-hero-panel">
                    <div class="kes-testimonials-hero-stars">
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                        <span>★</span>
                    </div>

                    <strong>
                        {{ __('testimonials.verified_experiences') }}
                    </strong>

                    <small>
                        {{ __('testimonials.trusted_service') }}
                    </small>
                </div>
            </div>
        </section>

        <section class="kes-testimonials-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.testimonials') }}"
                    class="kes-testimonials-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-testimonials-filter-field">
                        <label for="testimonial-service">
                            {{ __('testimonials.service') }}
                        </label>

                        <select
                            id="testimonial-service"
                            name="service"
                        >
                            <option value="">
                                {{ __('testimonials.all_services') }}
                            </option>

                            @foreach ($serviceTypes as $serviceType)
                                <option
                                    value="{{ $serviceType }}"
                                    @selected(
                                        $selectedService === $serviceType
                                    )
                                >
                                    {{ $serviceType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-testimonials-filter-field">
                        <label for="testimonial-rating">
                            {{ app()->isLocale('ur')
                                ? 'ریٹنگ'
                                : 'Rating' }}
                        </label>

                        <select
                            id="testimonial-rating"
                            name="rating"
                        >
                            <option value="">
                                {{ __('testimonials.all_ratings') }}
                            </option>

                            @foreach ([5, 4, 3, 2, 1] as $rating)
                                <option
                                    value="{{ $rating }}"
                                    @selected(
                                        $selectedRating === $rating
                                    )
                                >
                                    {{ __('testimonials.rating_option', [
                                        'rating' => $rating,
                                    ]) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-testimonials-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('testimonials.filter') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.testimonials',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-testimonials-clear"
                        >
                            {{ __('testimonials.clear_filters') }}
                        </a>
                    </div>
                </form>

                <div class="kes-testimonials-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('testimonials.eyebrow') }}
                        </span>

                        <h2>
                            {{ __('testimonials.results_title') }}
                        </h2>
                    </div>

                    <strong>
                        {{ __('testimonials.reviews_count', [
                            'count' => $testimonials->total(),
                        ]) }}
                    </strong>
                </div>

                <div class="kes-testimonials-grid">
                    @forelse ($testimonials as $testimonial)
                        @php
                            $customerName =
                                $testimonial
                                    ->localized_customer_name;

                            $initials = collect(
                                preg_split(
                                    '/\s+/u',
                                    trim($customerName),
                                ),
                            )
                                ->filter()
                                ->take(2)
                                ->map(
                                    fn ($part) => mb_strtoupper(
                                        mb_substr($part, 0, 1),
                                    ),
                                )
                                ->implode('');

                            $locationParts = collect([
                                $testimonial->localized_location,
                                $testimonial->country,
                            ])->filter()->implode(', ');
                        @endphp

                        <article
                            class="kes-testimonial-card {{ $testimonial->is_featured
                                ? 'is-featured'
                                : '' }}"
                        >
                            <div class="kes-testimonial-card-top">
                                <div class="kes-testimonial-customer">
                                    @if (filled($testimonial->photo_path))
                                        <img
                                            src="{{ asset(
                                                'storage/'
                                                .$testimonial->photo_path,
                                            ) }}"
                                            alt="{{ $customerName }}"
                                            loading="lazy"
                                        >
                                    @else
                                        <span
                                            class="kes-testimonial-avatar"
                                            aria-hidden="true"
                                        >
                                            {{ $initials ?: 'KA' }}
                                        </span>
                                    @endif

                                    <div>
                                        <h2>{{ $customerName }}</h2>

                                        @if (
                                            filled(
                                                $testimonial
                                                    ->localized_designation
                                            )
                                            || filled(
                                                $testimonial->company_name
                                            )
                                        )
                                            <p>
                                                {{ collect([
                                                    $testimonial
                                                        ->localized_designation,
                                                    $testimonial
                                                        ->company_name,
                                                ])->filter()->implode(' · ') }}
                                            </p>
                                        @endif

                                        @if (filled($locationParts))
                                            <small>{{ $locationParts }}</small>
                                        @endif
                                    </div>
                                </div>

                                @if ($testimonial->is_featured)
                                    <span
                                        class="kes-testimonial-featured"
                                    >
                                        {{ __('testimonials.featured') }}
                                    </span>
                                @endif
                            </div>

                            <div
                                class="kes-testimonial-stars"
                                aria-label="{{ $testimonial->rating }} out of 5 stars"
                            >
                                @for ($star = 1; $star <= 5; $star++)
                                    <span
                                        class="{{ $star <= $testimonial->rating
                                            ? 'is-filled'
                                            : '' }}"
                                    >
                                        ★
                                    </span>
                                @endfor
                            </div>

                            <blockquote>
                                “{{ $testimonial->localized_testimonial }}”
                            </blockquote>

                            <div class="kes-testimonial-card-footer">
                                <div class="kes-testimonial-meta">
                                    @if (filled($testimonial->service_type))
                                        <span>
                                            <small>
                                                {{ __('testimonials.service') }}
                                            </small>

                                            <strong>
                                                {{ $testimonial->service_type }}
                                            </strong>
                                        </span>
                                    @endif

                                    @if ($testimonial->travel_date)
                                        <span>
                                            <small>
                                                {{ __('testimonials.travel_date') }}
                                            </small>

                                            <strong>
                                                {{ $testimonial
                                                    ->travel_date
                                                    ->format('d M Y') }}
                                            </strong>
                                        </span>
                                    @endif
                                </div>

                                @if (filled($testimonial->video_url))
                                    <a
                                        href="{{ $testimonial->video_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="kes-testimonial-video-link"
                                    >
                                        <span>▶</span>

                                        {{ __('testimonials.watch_video') }}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="kes-testimonials-empty">
                            <span>★</span>

                            <h2>
                                {{ __('testimonials.no_items_title') }}
                            </h2>

                            <p>
                                {{ __('testimonials.no_items_text') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                @if ($testimonials->hasPages())
                    <div class="kes-testimonials-pagination">
                        {{ $testimonials->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
@endsection