@extends('website.layout')

@section('content')
    <section class="kes-page-header">
        <div class="kes-container">
            <h1>{{ $page['heading'] }}</h1>
            <p>{{ $page['description'] }}</p>
        </div>
    </section>

    <section class="kes-section">
        <div class="kes-container">
            <div class="kes-content-box">
                <h2 style="color:#062f20; margin-top:0;">{{ $page['heading'] }}</h2>

                <p style="line-height:1.8;">{{ $page['content'] }}</p>

                <div class="kes-urdu-box">
                    {{ $page['urdu'] }}
                </div>

                @if (($page['title'] ?? '') === 'Contact Us')
                    <div style="margin-top:30px;">
                        <h3 style="color:#062f20;">Contact Information</h3>
                        <p><strong>Phone:</strong> {{ $company->mobile ?? '+92 301 2592342' }}</p>
                        <p><strong>WhatsApp:</strong> {{ $company->whatsapp ?? '+92 301 2592342' }}</p>
                        <p><strong>Email:</strong> {{ $company->email ?? 'info@karwanealsaeed.com' }}</p>
                        <p><strong>Address:</strong> {{ $company->address ?? 'Office # 90,91 College Road, Chichawatni.' }}</p>

                        <div style="margin-top:20px; border-radius:22px; overflow:hidden; border:1px solid #e3eadf;">
                            <iframe
                                src="https://www.google.com/maps?q={{ urlencode($company->address ?? 'College Road Chichawatni Pakistan') }}&output=embed"
                                width="100%"
                                height="350"
                                style="border:0;"
                                loading="lazy">
                            </iframe>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection