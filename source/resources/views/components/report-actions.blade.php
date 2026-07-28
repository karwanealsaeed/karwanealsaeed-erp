<div
    class="kes-report-primary-actions no-print flex flex-wrap justify-end gap-3"
    data-karwan-report-primary-actions
>
    @if (auth()->user()?->canPrintReports())
        <button
            type="button"
            onclick="kesReportDownload('print')"
            class="kes-print-action rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-600"
            data-karwan-primary-print
        >
            Print Preview
        </button>
    @endif
</div>

@once
    <script src="{{ asset('js/report-actions.js') }}"></script>
@endonce
