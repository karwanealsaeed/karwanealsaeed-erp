(function () {
    window.kesReportDownload = function (format) {
        const slug = getCurrentReportSlug();

        if (!slug) {
            alert('Report slug nahi mila.');
            return;
        }

        const params = new URLSearchParams();

        document.querySelectorAll('input, select, textarea').forEach(function (field) {
            const model =
                field.getAttribute('wire:model.live') ||
                field.getAttribute('wire:model') ||
                field.getAttribute('wire:model.defer') ||
                field.getAttribute('wire:model.blur');

            if (!model) {
                return;
            }

            params.set(model, field.value || '');
        });

        let url = '/reports/' + slug + '/' + format;

        if (params.toString()) {
            url += '?' + params.toString();
        }

        if (format === 'print') {
            window.open(url, '_blank');
            return;
        }

        window.location.href = url;
    };

    function getCurrentReportSlug() {
        const parts = window.location.pathname
            .split('/')
            .filter(Boolean);

        return parts[parts.length - 1] || '';
    }
})();