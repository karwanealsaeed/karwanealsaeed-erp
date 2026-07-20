@extends('customer-portal.layout')

@section('title', 'Latest Offers & Promotions')

@section('content')
    <style>
        .portal-offers-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 22px;
        }

        .portal-offers-heading h1 {
            margin: 0 0 5px;
            color: #07513d;
            font-size: 29px;
        }

        .portal-offers-heading p {
            margin: 0;
            color: #667b74;
        }

        .portal-offers-heading a {
            color: #8a6816;
            font-weight: 700;
            text-decoration: none;
        }

        .portal-offers-grid {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .portal-offer-card {
            overflow: hidden;
            border: 1px solid rgba(7, 81, 61, .13);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 14px 38px rgba(6, 55, 42, .08);
        }

        .portal-offer-media {
            position: relative;
            display: flex;
            min-height: 190px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background:
                linear-gradient(135deg, #064735, #0a6a50);
            color: #dabb61;
            font-size: 56px;
            font-weight: 900;
            text-decoration: none;
        }

        .portal-offer-media img {
            width: 100%;
            height: 190px;
            object-fit: cover;
        }

        .portal-offer-featured {
            position: absolute;
            top: 13px;
            left: 13px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #dabb61;
            color: #18382f;
            font-size: 11px;
            font-weight: 900;
        }

        .portal-offer-body {
            padding: 20px;
        }

        .portal-offer-body small {
            color: #8a6816;
            font-weight: 800;
        }

        .portal-offer-body h2 {
            margin: 8px 0 10px;
            font-size: 20px;
        }

        .portal-offer-body h2 a {
            color: #143f33;
            text-decoration: none;
        }

        .portal-offer-body p {
            color: #61746f;
            line-height: 1.65;
        }

        .portal-offer-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 15px;
        }

        .portal-offer-code {
            padding: 7px 10px;
            border-radius: 999px;
            background: rgba(183, 144, 47, .13);
            color: #74570d;
            font-size: 12px;
            font-weight: 900;
        }

        .portal-offer-link {
            color: #07513d;
            font-weight: 800;
            text-decoration: none;
        }

        .portal-offers-empty {
            padding: 42px 25px;
            border: 1px dashed rgba(7, 81, 61, .25);
            border-radius: 18px;
            text-align: center;
            background: #fff;
        }

        .portal-offers-pagination {
            margin-top: 24px;
        }

        @media (max-width: 1000px) {
            .portal-offers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 700px) {
            .portal-offers-heading {
                display: block;
            }

            .portal-offers-heading a {
                display: inline-block;
                margin-top: 12px;
            }

            .portal-offers-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="portal-offers-heading">
        <div>
            <h1>Latest Offers & Promotions</h1>

            <p>
                Active travel offers exclusively available from
                Karwan E Al Saeed Travel & Tours.
            </p>
        </div>

        <a href="{{ route('website.promotions') }}">
            Open Public Offers Page →
        </a>
    </div>

    <div class="portal-offers-grid">
        @forelse ($promotions as $promotion)
            <article class="portal-offer-card">
                <a
                    href="{{ route(
                        'website.promotions.show',
                        ['promotion' => $promotion],
                    ) }}"
                    class="portal-offer-media"
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

                    @if ($promotion->is_featured)
                        <span class="portal-offer-featured">
                            Featured Offer
                        </span>
                    @endif
                </a>

                <div class="portal-offer-body">
                    @if (filled($promotion->promotion_type))
                        <small>
                            {{ $promotion->promotion_type }}
                        </small>
                    @endif

                    <h2>
                        <a href="{{ route(
                            'website.promotions.show',
                            ['promotion' => $promotion],
                        ) }}">
                            {{ $promotion->localized_title }}
                        </a>
                    </h2>

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
                                130,
                            ) }}
                        </p>
                    @endif

                    <div class="portal-offer-footer">
                        @if (filled($promotion->promo_code))
                            <span class="portal-offer-code">
                                Code: {{ $promotion->promo_code }}
                            </span>
                        @elseif ($promotion->ends_at)
                            <span class="portal-offer-code">
                                Ends:
                                {{ $promotion->ends_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="portal-offer-code">
                                Available Now
                            </span>
                        @endif

                        <a
                            href="{{ route(
                                'website.promotions.show',
                                ['promotion' => $promotion],
                            ) }}"
                            class="portal-offer-link"
                        >
                            View Offer →
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="portal-offers-empty">
                <h2>No active offers available</h2>

                <p>
                    New travel promotions will appear here
                    automatically.
                </p>
            </div>
        @endforelse
    </div>

    @if ($promotions->hasPages())
        <div class="portal-offers-pagination">
            {{ $promotions->links() }}
        </div>
    @endif
@endsection