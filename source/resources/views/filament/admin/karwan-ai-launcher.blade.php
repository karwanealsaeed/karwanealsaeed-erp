@if (\Filament\Facades\Filament::getCurrentPanel()?->getId() === 'admin')
    @once
        <link
            rel="stylesheet"
            href="{{ asset('css/karwan-ai.css') }}"
            data-karwan-ai-admin-style
        >

        @if (is_file(public_path('js/karwan-ai.js')))
            <script
                src="{{ asset('js/karwan-ai.js') }}"
                defer
                data-karwan-ai-admin-script
            ></script>
        @endif
    @endonce

    <div
        class="karwan-ai-admin-host"
        data-karwan-ai-admin-mode="public-information-only"
        aria-label="Karwan AI public information mode"
    >
        @include('components.karwan-ai-widget')
    </div>
@endif
