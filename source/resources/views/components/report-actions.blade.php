<div class="no-print flex flex-wrap justify-end gap-3">
    @if (auth()->user()?->canPrintReports())
        <button
            type="button"
            onclick="kesReportDownload('print')"
            class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-600"
        >
            Print
        </button>
    @endif

    @if (auth()->user()?->canDownloadReportPdf())
        <button
            type="button"
            onclick="kesReportDownload('pdf')"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-primary-500"
        >
            Download PDF
        </button>
    @endif

    @if (auth()->user()?->canDownloadReportExcel())
        <button
            type="button"
            onclick="kesReportDownload('excel')"
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-500"
        >
            Download Excel
        </button>
    @endif
</div>

@once
    <script src="{{ asset('js/report-actions.js') }}"></script>
@endonce