@if ($promotions->isNotEmpty())
    <section class="customer-latest-offers">
        <style>
            .customer-latest-offers {
                margin-top: 24px;
                padding: 24px;
                border: 1px solid rgba(183, 144, 47, .28);
                border-radius: 20px;
                background:
                    linear-gradient(
                        135deg,
                        rgba(10, 82, 61, .06),
                        rgba(183, 144, 47, .08)
                    ),
                    #fff;
            }

            .customer-latest-offers-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 18px;
            }

            .customer-latest-offers-header h2 {
                margin: 0;
                color: #07513d;
                font-size: 22px;
            }

            .customer-latest-offers-header a {
                color: #8a6816;
                font-weight: 700;
                text-decoration: none;
            }

            .customer-latest-offers-grid {
                display: grid;
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
                gap: 16px;
            }

            .customer-latest-offer-card {
                overflow: hidden;
                border: 1px solid rgba(7, 81, 61, .12);
                border-radius: 16px;
                background: #fff;
                box-shadow: 0 10px 30px rgba(6, 55, 42, .07);
            }

            .customer-latest-offer-image {
                display: flex;
                min-height: 145px;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                background: #07513d;
                color: #d7b95f;
                font-size: 44px;
                font-weight: 800;
            }

            .customer-latest-offer-image img {
                width: 100%;
                height: 145px;
                object-fit: cover;
            }

            .customer-latest-offer-body {
                padding: 16px;
            }

            .customer-latest-offer-body small {
                color: #8a6816;
                font-weight: 700;
            }

            .customer-latest-offer-body h3 {
                margin: 7px 0 9px;
                color: #153c32;
                font-size: 17px;
            }

            .customer-latest-offer-body h3 a {
                color: inherit;
                text-decoration: none;
            }

            .customer-latest-offer-body p {
                margin: 0 0 12px;
                color: #60736d;
                line-height: 1.6;
            }

            .customer-latest-offer-code {
                display: inline-flex;
                padding: 6px 10px;
                border-radius: 999px;
                background: rgba(183, 144, 47, .12);
                color: #75580d;
                font-size: 12px;
                font-weight: 800;
            }

            @media (max-width: 900px) {
                .customer-latest-offers-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>

        <div class="customer-latest-offers-header">
            <h2>Latest Offers & Promotions</h2>

            <a href="{{ route('customer.portal.promotions') }}">
                View All Offers →
            </a>
        </div>

        <div class="customer-latest-offers-grid">
            @foreach ($promotions as $promotion)
                <article class="customer-latest-offer-card">
                    <a
                        href="{{ route(
                            'website.promotions.show',
                            ['promotion' => $promotion],
                        ) }}"
                        class="customer-latest-offer-image"
                    >
                        @if (filled($promotion->featured_image_path))
                            <img
                                src="{{ asset(
                                    'storage/'
                                    .$promotion->featured_image_path,
                                ) }}"
                                alt="{{ $promotion->localized_title }}"
                                loading="lazy"
                            >
                        @else
                            <span>%</span>
                        @endif
                    </a>

                    <div class="customer-latest-offer-body">
                        @if (filled($promotion->promotion_type))
                            <small>
                                {{ $promotion->promotion_type }}
                            </small>
                        @endif

                        <h3>
                            <a href="{{ route(
                                'website.promotions.show',
                                ['promotion' => $promotion],
                            ) }}">
                                {{ $promotion->localized_title }}
                            </a>
                        </h3>

                        @if (
                            filled(
                                $promotion->localized_subtitle
                                ?: $promotion->localized_description
                            )
                        )
                            <p>
                                {{ \Illuminate\Support\Str::limit(
                                    trim(strip_tags(
                                        $promotion->localized_subtitle
                                        ?: $promotion
                                            ->localized_description
                                    )),
                                    95,
                                ) }}
                            </p>
                        @endif

                        @if (filled($promotion->promo_code))
                            <span class="customer-latest-offer-code">
                                Code: {{ $promotion->promo_code }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif