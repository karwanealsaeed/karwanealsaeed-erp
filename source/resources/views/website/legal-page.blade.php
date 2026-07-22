@extends('website.layout')

@section('content')
    @php
        $localeParameters = app()->isLocale('ur')
            ? ['lang' => 'ur']
            : [];

        $legalRoutes = [
            'privacy' => 'website.legal.privacy',
            'terms' => 'website.legal.terms',
            'refund' => 'website.legal.refund',
            'cancellation' => 'website.legal.cancellation',
            'disclaimer' => 'website.legal.disclaimer',
        ];
    @endphp

    <main class="kes-public-page-v2 kes-legal-page">
        <section class="kes-page-v2-hero">
            <div class="kes-page-v2-pattern"></div>

            <div class="kes-v2-container kes-page-v2-hero-inner">
                <div>
                    <nav class="kes-page-v2-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('website.home') }}">{{ __('website.home') }}</a>
                        <span>/</span>
                        <span>{{ $page['title'] }}</span>
                    </nav>

                    <span class="kes-page-v2-label">{{ $company->company_name }}</span>
                    <h1>{{ $page['heading'] }}</h1>
                    <p>{{ $page['description'] }}</p>
                </div>

                <div class="kes-page-v2-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ $company->company_name }}">
                </div>
            </div>
        </section>

        <section class="kes-legal-content">
            <div class="kes-v2-container kes-legal-layout">
                <article class="kes-legal-document">
                    <header class="kes-legal-intro">
                        <p class="kes-legal-effective">
                            <strong>{{ __('legal.effective_label') }}:</strong>
                            {{ __('legal.effective_date') }}
                        </p>

                        <p>{{ $page['summary'] }}</p>

                        <div class="kes-legal-notice" role="note">
                            {{ __('legal.notice') }}
                        </div>
                    </header>

                    @foreach ($page['sections'] as $section)
                        <section class="kes-legal-section">
                            <h2>{{ $section['heading'] }}</h2>

                            @foreach ($section['paragraphs'] as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach

                            @if ($section['bullets'] !== [])
                                <ul>
                                    @foreach ($section['bullets'] as $bullet)
                                        <li>{{ $bullet }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach
                </article>

                <aside class="kes-legal-sidebar">
                    <div class="kes-legal-side-card">
                        <h2>{{ __('legal.related') }}</h2>

                        <nav aria-label="{{ __('legal.footer_label') }}">
                            @foreach ($legalRoutes as $key => $routeName)
                                <a
                                    href="{{ route($routeName, $localeParameters) }}"
                                    @if ($key === $legalKey) aria-current="page" @endif
                                >
                                    {{ __("legal.links.{$key}") }}
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="kes-legal-side-card">
                        <h2>{{ __('legal.contact_heading') }}</h2>
                        <p>{{ __('legal.contact_text') }}</p>

                        <address>
                            <strong>{{ $company->company_name }}</strong>
                            <span>{{ $company->address }}</span>

                            <a
                                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->whatsapp ?: $company->mobile) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ $company->mobile }}
                            </a>

                            <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                        </address>

                        <a
                            class="kes-v2-btn kes-v2-btn-primary"
                            href="{{ route('website.contact', $localeParameters) }}"
                        >
                            {{ __('website.contact_us') }}
                        </a>
                    </div>
                </aside>
            </div>
        </section>
    </main>
@endsection
