@php
    $homeContent = app(\App\Services\HomepageContentService::class)->content();
    $homepageTestimonials = $homeContent['homepageTestimonials'];
    $homepageNews = $homeContent['homepageNews'];
    $homepagePromotions = $homeContent['homepagePromotions'];
    $isUrduHomepage = app()->isLocale('ur');
@endphp

<section class="section home-live-content" id="latest-updates">
    <div class="shell">
        <header class="section-head reveal" data-reveal>
            <div>
                <span class="eyebrow">
                    {{ $isUrduHomepage ? 'تازہ ترین معلومات' : 'Fresh from Karwan' }}
                </span>

                <h2>
                    {{ $isUrduHomepage
                        ? 'اعتماد، خبریں اور خصوصی آفرز۔'
                        : 'Trusted reviews, travel news and current offers.' }}
                </h2>
            </div>

            <p>
                {{ $isUrduHomepage
                    ? 'حقیقی کسٹمر تجربات اور ہماری تازہ ترین سفری اپڈیٹس ایک ہی جگہ دیکھیں۔'
                    : 'See genuine customer experiences and the latest published updates from our travel team.' }}
            </p>
        </header>

        <div class="home-live-content__grid">
            <section class="home-live-panel home-live-panel--reviews reveal" data-reveal>
                <div class="home-live-panel__head">
                    <div>
                        <span>{{ $isUrduHomepage ? 'کسٹمر ریویوز' : 'Customer Reviews' }}</span>
                        <h3>{{ $isUrduHomepage ? 'مسافروں کا اعتماد' : 'What travellers say' }}</h3>
                    </div>

                    <a href="{{ route('website.testimonials') }}">
                        {{ $isUrduHomepage ? 'تمام ریویوز' : 'All reviews' }}
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="home-live-list">
                    @forelse ($homepageTestimonials as $testimonial)
                        <article class="home-review-card">
                            <div class="home-review-card__stars" aria-label="{{ $testimonial['rating'] }} out of 5 stars">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span class="{{ $star <= $testimonial['rating'] ? 'is-active' : '' }}">★</span>
                                @endfor
                            </div>

                            <blockquote>“{{ $testimonial['content'] }}”</blockquote>

                            <footer>
                                <span class="home-review-card__avatar" aria-hidden="true">
                                    {{ mb_strtoupper(mb_substr($testimonial['name'], 0, 1)) }}
                                </span>

                                <div>
                                    <strong>{{ $testimonial['name'] }}</strong>
                                    <small>{{ $testimonial['role'] }}</small>
                                </div>
                            </footer>
                        </article>
                    @empty
                        <div class="home-live-empty">
                            <strong>{{ $isUrduHomepage ? 'ریویوز جلد شامل ہوں گے' : 'Reviews coming soon' }}</strong>
                            <p>{{ $isUrduHomepage ? 'نئے تصدیق شدہ کسٹمر تجربات یہاں دکھائے جائیں گے۔' : 'New verified customer experiences will appear here.' }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="home-live-panel reveal" data-reveal>
                <div class="home-live-panel__head">
                    <div>
                        <span>{{ $isUrduHomepage ? 'تازہ خبریں' : 'Latest News' }}</span>
                        <h3>{{ $isUrduHomepage ? 'سفری اپڈیٹس' : 'Travel updates' }}</h3>
                    </div>

                    <a href="{{ route('website.blog') }}">
                        {{ $isUrduHomepage ? 'تمام خبریں' : 'All news' }}
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="home-live-list">
                    @forelse ($homepageNews as $post)
                        <article class="home-news-card">
                            <div class="home-news-card__meta">
                                <span>{{ $post['category'] }}</span>
                                @if ($post['date'] !== '')
                                    <time>{{ $post['date'] }}</time>
                                @endif
                            </div>

                            <h4>{{ $post['title'] }}</h4>

                            @if ($post['excerpt'] !== '')
                                <p>{{ $post['excerpt'] }}</p>
                            @endif

                            <a href="{{ $post['url'] }}">
                                {{ $isUrduHomepage ? 'خبر پڑھیں' : 'Read article' }}
                                <span aria-hidden="true">→</span>
                            </a>
                        </article>
                    @empty
                        <div class="home-live-empty">
                            <strong>{{ $isUrduHomepage ? 'نئی خبریں جلد آئیں گی' : 'New updates coming soon' }}</strong>
                            <p>{{ $isUrduHomepage ? 'تازہ شائع شدہ سفری خبریں یہاں نظر آئیں گی۔' : 'The latest published travel news will appear here.' }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="home-live-panel home-live-panel--promotions reveal" data-reveal>
                <div class="home-live-panel__head">
                    <div>
                        <span>{{ $isUrduHomepage ? 'تازہ پروموشنز' : 'Latest Promotions' }}</span>
                        <h3>{{ $isUrduHomepage ? 'موجودہ خصوصی آفرز' : 'Current special offers' }}</h3>
                    </div>

                    <a href="{{ route('website.promotions') }}">
                        {{ $isUrduHomepage ? 'تمام آفرز' : 'All offers' }}
                        <span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="home-live-list">
                    @forelse ($homepagePromotions as $promotion)
                        <article class="home-promotion-card">
                            <span class="home-promotion-card__type">{{ $promotion['type'] }}</span>

                            <h4>{{ $promotion['title'] }}</h4>

                            @if ($promotion['excerpt'] !== '')
                                <p>{{ $promotion['excerpt'] }}</p>
                            @endif

                            <div class="home-promotion-card__footer">
                                @if ($promotion['code'] !== '')
                                    <span class="home-promotion-card__code">
                                        {{ $isUrduHomepage ? 'کوڈ' : 'Code' }}:
                                        {{ $promotion['code'] }}
                                    </span>
                                @endif

                                <a href="{{ $promotion['url'] }}">
                                    {{ $isUrduHomepage ? 'آفر دیکھیں' : 'View offer' }}
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="home-live-empty">
                            <strong>{{ $isUrduHomepage ? 'نئی آفرز جلد آئیں گی' : 'New promotions coming soon' }}</strong>
                            <p>{{ $isUrduHomepage ? 'فعال اور موجودہ پروموشنز یہاں دکھائی جائیں گی۔' : 'Active current promotions will appear here.' }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</section>
