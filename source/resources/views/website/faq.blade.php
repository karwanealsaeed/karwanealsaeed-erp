@extends('website.layout')

@section('content')
    <main class="kes-faq-page">
        <section class="kes-faq-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-faq-hero-inner">
                <div class="kes-faq-hero-copy">
                    <nav
                        class="kes-page-v2-breadcrumb"
                        aria-label="Breadcrumb"
                    >
                        <a href="{{ route('website.home') }}">
                            {{ __('faq.home') }}
                        </a>

                        <span>/</span>

                        <span>{{ __('faq.title') }}</span>
                    </nav>

                    <span class="kes-v2-section-label">
                        {{ __('faq.eyebrow') }}
                    </span>

                    <h1>{{ __('faq.title') }}</h1>

                    <p>{{ __('faq.subtitle') }}</p>
                </div>

                <div
                    class="kes-faq-hero-symbol"
                    aria-hidden="true"
                >
                    <span>?</span>
                </div>
            </div>
        </section>

        <section class="kes-faq-content">
            <div class="kes-v2-container">
                <form
                    method="GET"
                    action="{{ route('website.faq') }}"
                    class="kes-faq-filters"
                >
                    @if (app()->isLocale('ur'))
                        <input
                            type="hidden"
                            name="lang"
                            value="ur"
                        >
                    @endif

                    <div class="kes-faq-filter-field">
                        <label for="faq-category">
                            {{ __('faq.category') }}
                        </label>

                        <select
                            id="faq-category"
                            name="category"
                        >
                            <option value="">
                                {{ __('faq.all_categories') }}
                            </option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category }}"
                                    @selected(
                                        $selectedCategory === $category
                                    )
                                >
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="kes-faq-filter-actions">
                        <button
                            type="submit"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('faq.filter') }}
                        </button>

                        <a
                            href="{{ route(
                                'website.faq',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-faq-clear"
                        >
                            {{ __('faq.clear_filter') }}
                        </a>
                    </div>
                </form>

                <div class="kes-faq-results-heading">
                    <div>
                        <span class="kes-v2-section-label">
                            {{ __('faq.eyebrow') }}
                        </span>

                        <h2>{{ __('faq.results_title') }}</h2>
                    </div>

                    <strong>
                        {{ trans_choice(
                            'faq.questions_count',
                            $faqs->total(),
                            ['count' => $faqs->total()],
                        ) }}
                    </strong>
                </div>

                <div class="kes-faq-layout">
                    <div class="kes-faq-list">
                        @forelse ($faqs as $faq)
                            <details
                                class="kes-faq-item {{ $faq->is_featured
                                    ? 'is-featured'
                                    : '' }}"
                                @if ($loop->first) open @endif
                            >
                                <summary>
                                    <span class="kes-faq-question-number">
                                        {{ str_pad(
                                            (string) (
                                                ($faqs->currentPage() - 1)
                                                * $faqs->perPage()
                                                + $loop->iteration
                                            ),
                                            2,
                                            '0',
                                            STR_PAD_LEFT,
                                        ) }}
                                    </span>

                                    <span class="kes-faq-question-copy">
                                        @if (
                                            filled(
                                                $faq->localized_category
                                            )
                                        )
                                            <small>
                                                {{ $faq->localized_category }}
                                            </small>
                                        @endif

                                        <strong>
                                            {{ $faq->localized_question }}
                                        </strong>
                                    </span>

                                    @if ($faq->is_featured)
                                        <span class="kes-faq-featured">
                                            {{ __('faq.featured') }}
                                        </span>
                                    @endif

                                    <span
                                        class="kes-faq-toggle"
                                        aria-hidden="true"
                                    ></span>
                                </summary>

                                <div class="kes-faq-answer">
                                    {!! nl2br(
                                        e($faq->localized_answer),
                                    ) !!}
                                </div>
                            </details>
                        @empty
                            <div class="kes-faq-empty">
                                <span>?</span>

                                <h2>{{ __('faq.no_items_title') }}</h2>

                                <p>{{ __('faq.no_items_text') }}</p>
                            </div>
                        @endforelse

                        @if ($faqs->hasPages())
                            <div class="kes-faq-pagination">
                                {{ $faqs->links() }}
                            </div>
                        @endif
                    </div>

                    <aside class="kes-faq-support-card">
                        <span class="kes-faq-support-icon">✦</span>

                        <h2>{{ __('faq.support_title') }}</h2>

                        <p>{{ __('faq.support_text') }}</p>

                        <a
                            href="{{ route(
                                'website.contact',
                                app()->isLocale('ur')
                                    ? ['lang' => 'ur']
                                    : [],
                            ) }}"
                            class="kes-v2-btn kes-v2-btn-gold"
                        >
                            {{ __('faq.contact_us') }}
                        </a>
                    </aside>
                </div>
            </div>
        </section>
    </main>
@endsection