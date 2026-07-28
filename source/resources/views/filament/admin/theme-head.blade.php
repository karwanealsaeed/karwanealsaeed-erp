<link rel="stylesheet" href="{{ asset('css/admin-theme.css') }}?v={{ file_exists(public_path('css/admin-theme.css')) ? filemtime(public_path('css/admin-theme.css')) : time() }}">

{{-- KES PREMIUM UI FOUNDATION START --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap"
    rel="stylesheet"
>
<link rel="stylesheet" href="{{ asset('css/karwan-premium-ui.css') }}">
{{-- PHASE 16E-3F4 SHARED PUBLIC V3 PORTAL FOUNDATION START --}}
<link
    rel="stylesheet"
    href="{{ asset('css/karwan-portal-premium-studio-v3.css') }}?v={{ filemtime(public_path('css/karwan-portal-premium-studio-v3.css')) }}"
>
{{-- PHASE 16E-3F4 SHARED PUBLIC V3 PORTAL FOUNDATION END --}}
<script src="{{ asset('js/karwan-premium-ui.js') }}" defer></script>
{{-- KES PREMIUM UI FOUNDATION END --}}


{{-- PHASE 16F-3C2 UNIVERSAL REPORT EXPORT START --}}
<meta
    name="karwan-report-export-url"
    content="{{ route('reports.export', ['report' => 'visible', 'format' => '__FORMAT__']) }}"
>
<meta
    name="karwan-current-admin-route"
    content="{{ request()->route()?->getName() }}"
>
<meta
    name="karwan-report-csrf-token"
    content="{{ csrf_token() }}"
>
{{-- PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION START --}}
<meta
    name="karwan-report-can-view"
    content="{{ (auth()->user()?->canViewReports() ?? false) ? '1' : '0' }}"
>
<meta
    name="karwan-report-can-pdf"
    content="{{ (
        (auth()->user()?->canViewReports() ?? false)
        && (auth()->user()?->canDownloadReportPdf() ?? false)
    ) ? '1' : '0' }}"
>
<meta
    name="karwan-report-can-excel"
    content="{{ (
        (auth()->user()?->canViewReports() ?? false)
        && (auth()->user()?->canDownloadReportExcel() ?? false)
    ) ? '1' : '0' }}"
>
{{-- PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION END --}}
<script
    src="{{ asset('js/karwan-report-export-v3.js') }}?v={{ file_exists(public_path('js/karwan-report-export-v3.js')) ? filemtime(public_path('js/karwan-report-export-v3.js')) : time() }}"
    defer
></script>
{{-- PHASE 16F-3C2 UNIVERSAL REPORT EXPORT END --}}
