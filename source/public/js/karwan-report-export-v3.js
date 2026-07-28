(() => {
    'use strict';

    const state = {
        scheduled: false,
        observer: null,
    };

    const exportMeta = () =>
        document.querySelector(
            'meta[name="karwan-report-export-url"]',
        );

    const currentRoute = () =>
        document.querySelector(
            'meta[name="karwan-current-admin-route"]',
        )?.content?.trim() || '';

    const csrfToken = () =>
        document.querySelector(
            'meta[name="karwan-report-csrf-token"]',
        )?.content?.trim()
        || document.querySelector(
            'meta[name="csrf-token"]',
        )?.content?.trim()
        || '';

    // PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION START
    const exportPermission = (format) =>
        document.querySelector(
            `meta[name="karwan-report-can-${format}"]`,
        )?.content?.trim() === '1';

    const permittedFormats = () =>
        ['pdf', 'excel'].filter(exportPermission);
    // PHASE 16F-3F10A REPORT EXPORT PERMISSION RECONCILIATION END

    const normalizedText = (element) =>
        (element?.textContent || '')
            .replace(/\s+/g, ' ')
            .trim();

    const isPrintControl = (element) => {
        if (!(element instanceof HTMLElement)) {
            return false;
        }

        if (
            element.dataset.karwanExportAction
            || element.closest('[data-karwan-export-actions]')
        ) {
            return false;
        }

        const onclick =
            element.getAttribute('onclick') || '';
        const text = normalizedText(element);

        return onclick.includes('window.print')
            || /(^|\s)print(\s|$)/i.test(text);
    };

    const actionIcon = (type) => {
        if (type === 'pdf') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M7 3h7l4 4v14H7z"/>
                    <path d="M14 3v5h5M9 13h6M9 17h4"/>
                </svg>
            `;
        }

        return `
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 3h14v18H5z"/>
                <path d="M8 8h8M8 12h8M8 16h8M11 6v12"/>
            </svg>
        `;
    };

    const makeAction = (type) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className =
            `kes-report-export-action kes-report-export-action--${type}`;
        button.dataset.karwanExportAction = type;
        button.innerHTML =
            actionIcon(type)
            + `<span>${type === 'pdf'
                ? 'PDF Download'
                : 'Excel Export'}</span>`;

        button.addEventListener('click', () => {
            exportVisibleReport(type, button);
        });

        return button;
    };

    const createActionGroup = () => {
        const formats = permittedFormats();

        if (formats.length === 0) {
            return null;
        }

        const group = document.createElement('span');
        group.className =
            'kes-report-export-actions';
        group.dataset.karwanExportActions = '1';

        formats.forEach((format) => {
            group.append(makeAction(format));
        });

        return group;
    };

    const installActions = () => {
        const routeName = currentRoute();

        if (
            !routeName.startsWith(
                'filament.admin.pages.',
            )
            || !exportMeta()
            || !exportPermission('view')
        ) {
            return;
        }

        document.querySelectorAll(
            '[data-karwan-report-primary-actions]',
        ).forEach((container) => {
            const hasUniversalGroup = Array.from(
                container.children,
            ).some((child) =>
                child.matches(
                    '[data-karwan-export-actions]',
                ),
            );

            if (hasUniversalGroup) {
                return;
            }

            const group = createActionGroup();

            if (group) {
                container.append(group);
            }
        });

        const controls = Array.from(
            document.querySelectorAll(
                'button, a, [role="button"]',
            ),
        )
            .filter(isPrintControl)
            .filter(
                (control) =>
                    ! control.closest(
                        '[data-karwan-report-primary-actions]',
                    ),
            );

        controls.forEach((printControl) => {
            if (
                printControl.dataset
                    .karwanExportEnhanced === '1'
            ) {
                return;
            }

            printControl.dataset
                .karwanExportEnhanced = '1';
            printControl.classList.add(
                'kes-print-action',
            );

            const group = createActionGroup();

            if (! group) {
                return;
            }

            printControl.insertAdjacentElement(
                'afterend',
                group,
            );
        });
    };

    const replaceInteractiveFields = (clone) => {
        clone.querySelectorAll(
            'input, select, textarea',
        ).forEach((field) => {
            const replacement =
                document.createElement('span');
            replacement.className =
                'kes-export-field-value';

            let value = '';

            if (
                field instanceof HTMLSelectElement
            ) {
                value =
                    field.selectedOptions[0]
                        ?.textContent || field.value;
            } else {
                value = field.value;
            }

            replacement.textContent =
                value?.trim() || '—';
            field.replaceWith(replacement);
        });
    };

    const cleanClone = (clone) => {
        replaceInteractiveFields(clone);

        clone.querySelectorAll(
            [
                'script',
                'style',
                'link',
                'meta',
                'button',
                'nav',
                'aside',
                '.fi-sidebar',
                '.fi-topbar',
                '[data-karwan-export-actions]',
                '.kes-print-action',
                '.karwan-ai-widget',
                '[class*="karwan-ai"]',
                '[class*="no-print"]',
                '[class*="-no-print"]',
            ].join(','),
        ).forEach((element) => {
            element.remove();
        });

        clone.querySelectorAll('*').forEach(
            (element) => {
                Array.from(
                    element.attributes,
                ).forEach((attribute) => {
                    const name =
                        attribute.name.toLowerCase();

                    if (
                        name.startsWith('on')
                        || name.startsWith('wire:')
                        || name.startsWith('x-')
                        || name.startsWith('@')
                    ) {
                        element.removeAttribute(
                            attribute.name,
                        );
                    }
                });
            },
        );
    };

    const tablePayload = (root) =>
        Array.from(
            root.querySelectorAll('table'),
        )
            .slice(0, 30)
            .map((table, index) => {
                const caption =
                    table.querySelector('caption')
                        ?.textContent?.trim()
                    || table.closest(
                        'section, article, div',
                    )?.querySelector(
                        'h2, h3, h4',
                    )?.textContent?.trim()
                    || `Table ${index + 1}`;

                const headerCells =
                    table.querySelectorAll(
                        'thead th',
                    );
                let headers = Array.from(
                    headerCells,
                ).map(normalizedText);

                const bodyRows = Array.from(
                    table.querySelectorAll(
                        'tbody tr',
                    ),
                );

                let rows = bodyRows.map((row) =>
                    Array.from(
                        row.querySelectorAll(
                            'th, td',
                        ),
                    ).map(normalizedText),
                );

                if (
                    headers.length === 0
                    && rows.length > 0
                ) {
                    headers = rows.shift() || [];
                }

                rows = rows.slice(0, 10000);

                return {
                    title: caption,
                    headers,
                    rows,
                };
            });

    const textPayload = (root) => {
        const text =
            (root.innerText || '')
                .split(/\n+/)
                .map((line) =>
                    line.replace(/\s+/g, ' ').trim(),
                )
                .filter(Boolean);

        return Array.from(new Set(text))
            .slice(0, 1000);
    };

    const reportRoot = () =>
        document.querySelector(
            '.fi-page-content',
        )
        || document.querySelector('.fi-page')
        || document.querySelector('main');

    const reportTitle = () =>
        normalizedText(
            document.querySelector(
                '.fi-header-heading',
            ),
        )
        || normalizedText(
            document.querySelector(
                '.fi-page h1',
            ),
        )
        || document.title
        || 'Admin Report';

    const endpointFor = (format) => {
        const template =
            exportMeta()?.content || '';

        return template.replace(
            '__FORMAT__',
            format,
        );
    };

    const downloadBlob = (
        blob,
        filename,
    ) => {
        const url =
            URL.createObjectURL(blob);
        const anchor =
            document.createElement('a');

        anchor.href = url;
        anchor.download = filename;
        anchor.hidden = true;
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();

        window.setTimeout(
            () => URL.revokeObjectURL(url),
            1500,
        );
    };

    const responseFilename = (
        response,
        fallback,
    ) => {
        const disposition =
            response.headers.get(
                'content-disposition',
            ) || '';
        const match =
            disposition.match(
                /filename="?([^";]+)"?/i,
            );

        return match?.[1] || fallback;
    };

    async function exportVisibleReport(
        format,
        button,
    ) {
        const root = reportRoot();
        const routeName = currentRoute();
        const endpoint = endpointFor(format);
        const token = csrfToken();

        if (
            !root
            || !endpoint
            || !routeName
            || !token
        ) {
            window.alert(
                'Report export configuration incomplete hai.',
            );
            return;
        }

        const clone =
            root.cloneNode(true);
        cleanClone(clone);

        const payload = {
            title: reportTitle(),
            source_url: window.location.href,
            source_route: routeName,
            html: clone.innerHTML,
            tables: tablePayload(root),
            text_rows: textPayload(clone),
        };

        const originalHtml =
            button.innerHTML;
        button.disabled = true;
        button.classList.add('is-loading');
        button.innerHTML =
            '<span>Preparing…</span>';

        try {
            const response = await fetch(
                endpoint,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept':
                            'application/octet-stream',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                },
            );

            if (!response.ok) {
                let message =
                    `Export failed (${response.status}).`;

                try {
                    const error =
                        await response.json();
                    message =
                        error.message || message;
                } catch {
                    // Non-JSON error body.
                }

                throw new Error(message);
            }

            const blob =
                await response.blob();
            const fallback =
                `admin-report.${format === 'pdf'
                    ? 'pdf'
                    : 'xls'}`;

            downloadBlob(
                blob,
                responseFilename(
                    response,
                    fallback,
                ),
            );
        } catch (error) {
            console.error(
                'Karwan report export failed:',
                error,
            );
            window.alert(
                error instanceof Error
                    ? error.message
                    : 'Report export failed.',
            );
        } finally {
            button.disabled = false;
            button.classList.remove(
                'is-loading',
            );
            button.innerHTML = originalHtml;
        }
    }

    const scheduleInstall = () => {
        if (state.scheduled) {
            return;
        }

        state.scheduled = true;

        window.requestAnimationFrame(() => {
            state.scheduled = false;
            installActions();
        });
    };

    const boot = () => {
        scheduleInstall();

        state.observer =
            new MutationObserver(
                scheduleInstall,
            );
        state.observer.observe(
            document.documentElement,
            {
                childList: true,
                subtree: true,
            },
        );

        document.addEventListener(
            'livewire:navigated',
            scheduleInstall,
        );
    };

    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            boot,
            { once: true },
        );
    } else {
        boot();
    }
})();

/* PHASE 16F-3D2 UNIVERSAL REPORT TOOLBAR DEDUPLICATION START */
(() => {
    'use strict';

    const normalize = (value) => String(value || '')
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();

    const actionType = (element) => {
        const text = normalize(element.textContent);
        const href = normalize(element.getAttribute('href'));
        const onclick = normalize(element.getAttribute('onclick'));
        const explicit = normalize(
            element.dataset.karwanExportAction
            || element.dataset.reportAction,
        );

        if (
            explicit === 'pdf'
            || text === 'pdf download'
            || text === 'download pdf'
            || href.includes('/pdf')
            || onclick.includes("'pdf'")
            || onclick.includes('"pdf"')
        ) {
            return 'pdf';
        }

        if (
            explicit === 'excel'
            || text === 'excel export'
            || text === 'download excel'
            || text === 'download excel csv'
            || href.includes('/excel')
            || onclick.includes("'excel'")
            || onclick.includes('"excel"')
        ) {
            return 'excel';
        }

        return null;
    };

    const deduplicateReportActions = () => {
        const page = document.querySelector('.fi-page');

        if (! page) {
            return;
        }

        const universal = page.querySelector(
            '[data-karwan-export-actions]',
        );

        if (! universal) {
            return;
        }

        const controls = Array.from(
            page.querySelectorAll('a, button'),
        );

        ['pdf', 'excel'].forEach((type) => {
            const matches = controls.filter(
                (control) => actionType(control) === type,
            );

            const universalControl = matches.find(
                (control) => universal.contains(control),
            );

            if (! universalControl) {
                return;
            }

            matches.forEach((control) => {
                if (control === universalControl) {
                    return;
                }

                const container = control.closest(
                    '[data-karwan-report-primary-actions],'
                    + '.kes-report-export-actions,'
                    + '.flex.flex-wrap.gap-3',
                );

                control.remove();

                if (
                    container
                    && container !== universal
                    && ! container.querySelector('a, button')
                ) {
                    container.remove();
                }
            });
        });
    };

    const schedule = () => {
        window.requestAnimationFrame(deduplicateReportActions);
    };

    document.addEventListener('DOMContentLoaded', schedule);
    document.addEventListener('livewire:navigated', schedule);

    const observer = new MutationObserver(schedule);

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true,
    });
})();
/* PHASE 16F-3D2 UNIVERSAL REPORT TOOLBAR DEDUPLICATION END */

/* PHASE 16F-3D8C HR GLOBAL PRINT IFRAME BRIDGE START */
(() => {
    'use strict';

    const triggerSelector = '[data-kes-hr-print-preview]';
    const frameId = 'kes-hr-print-frame';

    const printCss = `
        @page {
            size: A4 landscape;
            margin: 6mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-width: 0;
            max-width: none;
            margin: 0;
            padding: 0;
            color: #111;
            background: #fff;
            font-family:
                Inter,
                "Segoe UI",
                Arial,
                sans-serif;
        }

        .hr-report-page {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            color: #111 !important;
            background: #fff !important;
        }

        .hr-report-page .hr-report-filters,
        .hr-report-page .hr-no-print,
        .hr-report-page [data-karwan-report-primary-actions],
        .hr-report-page [data-karwan-export-actions],
        .hr-report-page .kes-report-export-actions,
        .hr-report-page .kes-print-action,
        .hr-report-page button {
            display: none !important;
        }

        .hr-report-page .hr-report-panel {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            margin: 0 0 2.5mm !important;
            padding: 3mm !important;
            overflow: visible !important;
            border: 1px solid #d8dfdb !important;
            border-radius: 2.5mm !important;
            box-shadow: none !important;
            break-inside: auto !important;
            page-break-inside: auto !important;
            background: #fff !important;
        }

        .hr-report-page .hr-report-panel h2,
        .hr-report-page .hr-report-panel h3 {
            margin: 0 0 1mm !important;
            font-size: 11.5pt !important;
            line-height: 1.15 !important;
        }

        .hr-report-page .hr-report-panel p {
            margin: 0 0 1.5mm !important;
            font-size: 7.5pt !important;
            line-height: 1.25 !important;
        }

        .hr-report-page .hr-stat-grid {
            display: grid !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            grid-template-columns:
                repeat(3, minmax(0, 1fr)) !important;
            grid-auto-flow: row !important;
            gap: 2.2mm !important;
            margin: 0 0 2.5mm !important;
            align-items: stretch !important;
        }

        .hr-report-page .hr-stat-grid > .hr-stat-card,
        .hr-report-page .hr-stat-card {
            display: block !important;
            width: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 2.3mm 2.7mm !important;
            border-radius: 2.2mm !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .hr-report-page .hr-stat-label {
            display: block !important;
            margin: 0 0 .7mm !important;
            font-size: 6.8pt !important;
            line-height: 1.12 !important;
        }

        .hr-report-page .hr-stat-value {
            display: block !important;
            margin: 0 !important;
            font-size: 11pt !important;
            line-height: 1.05 !important;
        }

        .hr-report-page .hr-table-wrap {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            margin: 1.5mm 0 0 !important;
            overflow: visible !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .hr-report-page .hr-table {
            display: table !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            font-size: 6.8pt !important;
        }

        .hr-report-page .hr-table thead {
            display: table-header-group !important;
        }

        .hr-report-page .hr-table tfoot {
            display: table-footer-group !important;
        }

        .hr-report-page .hr-table tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .hr-report-page .hr-table th,
        .hr-report-page .hr-table td {
            min-width: 0 !important;
            max-width: none !important;
            padding: 1.3mm 1.5mm !important;
            border-bottom: 1px solid #d8dfdb !important;
            white-space: normal !important;
            overflow: visible !important;
            overflow-wrap: anywhere !important;
            word-break: normal !important;
            text-overflow: clip !important;
            vertical-align: top !important;
        }

        .hr-report-page .hr-table th {
            background: #f3f7f5 !important;
            color: #1f372f !important;
            font-weight: 700 !important;
        }

        .hr-report-page .hr-badge {
            display: inline-block !important;
            max-width: 100% !important;
            padding: .5mm 1mm !important;
            font-size: 6.2pt !important;
            line-height: 1.1 !important;
            white-space: normal !important;
        }

        .hr-report-page .hr-empty {
            padding: 2.5mm !important;
            font-size: 7.5pt !important;
        }
    `;

    const removeExistingFrame = () => {
        document.getElementById(frameId)?.remove();
    };

    const createFrame = () => {
        removeExistingFrame();

        const frame = document.createElement('iframe');

        frame.id = frameId;
        frame.setAttribute('title', 'HR print preview');
        frame.setAttribute('aria-hidden', 'true');

        Object.assign(frame.style, {
            position: 'fixed',
            right: '0',
            bottom: '0',
            width: '1px',
            height: '1px',
            border: '0',
            opacity: '0',
            pointerEvents: 'none',
        });

        document.body.appendChild(frame);

        return frame;
    };

    const waitForPrintableDocument = async (printDocument) => {
        if (printDocument.fonts?.ready) {
            try {
                await printDocument.fonts.ready;
            } catch (error) {
                console.warn(
                    'HR print font readiness check failed.',
                    error,
                );
            }
        }

        await new Promise((resolve) => {
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(resolve);
            });
        });
    };

    const cleanupFrame = (frame) => {
        window.setTimeout(() => {
            frame.remove();
        }, 250);
    };

    const openHrPrintPreview = async () => {
        const source = document.querySelector('.hr-report-page');

        if (! source) {
            console.error('HR report source was not found.');

            return false;
        }

        const frame = createFrame();
        const printWindow = frame.contentWindow;
        const printDocument = frame.contentDocument;

        if (! printWindow || ! printDocument) {
            frame.remove();
            console.error('HR print frame could not be created.');

            return false;
        }

        printDocument.open();
        printDocument.write(
            '<!doctype html>'
            + '<html lang="en">'
            + '<head>'
            + '<meta charset="utf-8">'
            + '<meta name="viewport" '
            + 'content="width=device-width,initial-scale=1">'
            + '<title>HR Reports - Print Preview</title>'
            + '</head>'
            + '<body></body>'
            + '</html>',
        );
        printDocument.close();

        const style = printDocument.createElement('style');

        style.id = 'karwan-hr-iframe-print-style';
        style.textContent = printCss;

        printDocument.head.appendChild(style);

        const clone = source.cloneNode(true);

        clone.querySelectorAll(
            '.hr-report-filters,'
            + '.hr-no-print,'
            + '[data-karwan-report-primary-actions],'
            + '[data-karwan-export-actions],'
            + '.kes-report-export-actions,'
            + '.kes-print-action,'
            + 'button,'
            + 'script',
        ).forEach((element) => {
            element.remove();
        });

        printDocument.body.appendChild(
            printDocument.importNode(
                clone,
                true,
            ),
        );

        await waitForPrintableDocument(printDocument);

        printWindow.addEventListener(
            'afterprint',
            () => {
                cleanupFrame(frame);
            },
            { once: true },
        );

        printWindow.focus();
        printWindow.print();

        window.setTimeout(() => {
            if (document.body.contains(frame)) {
                cleanupFrame(frame);
            }
        }, 60000);

        return false;
    };

    window.kesHrPrintPreview = openHrPrintPreview;

    document.addEventListener(
        'click',
        (event) => {
            const trigger = event.target.closest(triggerSelector);

            if (! trigger) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            void openHrPrintPreview();
        },
        true,
    );
})();
/* PHASE 16F-3D8C HR GLOBAL PRINT IFRAME BRIDGE END */

/* PHASE 16F-3D9 THREE CONFIRMED REPORT PRINT FIXES START */
(() => {
    'use strict';

    const reportConfigs = new Map([
        [
            '/admin/passport-validity-report',
            {
                key: 'passport-validity',
                title: 'Passport Validity Report',
            },
        ],
        [
            '/admin/supplier-aging-report',
            {
                key: 'supplier-aging',
                title: 'Supplier Aging Report',
            },
        ],
        [
            '/admin/travel-insurance-report',
            {
                key: 'travel-insurance',
                title: 'Travel Insurance Report',
            },
        ],
    ]);

    const frameId = 'kes-confirmed-report-print-frame';

    const normalizedText = (element) => String(
        element?.textContent || '',
    )
        .replace(/\s+/g, ' ')
        .trim()
        .toLowerCase();

    const isPrintControl = (element) => {
        if (! element) {
            return false;
        }

        const onclick = String(
            element.getAttribute('onclick') || '',
        ).toLowerCase();

        const explicit = String(
            element.dataset.karwanExportAction
            || element.dataset.reportAction
            || '',
        ).toLowerCase();

        const text = normalizedText(element);

        return element.matches(
            '[data-karwan-primary-print],'
            + '.kes-print-action,'
            + '[data-kes-special-report-print]',
        )
            || explicit === 'print'
            || onclick.includes("kesreportdownload('print')")
            || onclick.includes('kesreportdownload("print")')
            || onclick.includes('window.print')
            || text === 'print'
            || text === 'print report'
            || text === 'print preview';
    };

    const printCss = `
        @page {
            size: A4 landscape;
            margin: 6mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-width: 0;
            max-width: none;
            margin: 0;
            padding: 0;
            color: #111;
            background: #fff;
            font-family:
                Inter,
                "Segoe UI",
                Arial,
                sans-serif;
        }

        .kes-confirmed-print-root {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            color: #111 !important;
            background: #fff !important;
        }

        .kes-confirmed-print-root :where(
            form,
            nav,
            aside,
            button,
            script,
            style,
            link,
            .no-print,
            [class*="no-print"],
            [class*="-no-print"],
            [data-karwan-report-primary-actions],
            [data-karwan-export-actions],
            .kes-report-export-actions,
            .kes-print-action,
            .fi-header-actions,
            .fi-breadcrumbs,
            .fi-pagination,
            .fi-ta-actions,
            .fi-ta-header-toolbar,
            .karwan-ai-widget,
            [class*="karwan-ai"]
        ) {
            display: none !important;
        }

        .kes-confirmed-print-root h1,
        .kes-confirmed-print-root h2 {
            margin: 0 0 1.5mm !important;
            font-size: 15pt !important;
            line-height: 1.15 !important;
        }

        .kes-confirmed-print-root h3 {
            margin: 0 0 1mm !important;
            font-size: 11pt !important;
            line-height: 1.15 !important;
        }

        .kes-confirmed-print-root p {
            margin: 0 0 1.5mm !important;
            font-size: 7.5pt !important;
            line-height: 1.25 !important;
        }

        .kes-confirmed-print-root > *,
        .kes-confirmed-print-root section,
        .kes-confirmed-print-root article,
        .kes-confirmed-print-root [class*="panel"],
        .kes-confirmed-print-root [class*="card"],
        .kes-confirmed-print-root [class*="wrap"] {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .kes-confirmed-print-root [data-kes-print-summary-grid] {
            display: grid !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            grid-template-columns:
                repeat(3, minmax(0, 1fr)) !important;
            grid-auto-flow: row !important;
            gap: 2.2mm !important;
            margin: 0 0 3mm !important;
            align-items: stretch !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .kes-confirmed-print-root [data-kes-print-summary-card] {
            display: block !important;
            width: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 2.4mm 2.8mm !important;
            border: 1px solid #d8dfdb !important;
            border-radius: 2.2mm !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            background: #fff !important;
        }

        .kes-confirmed-print-root [data-kes-print-summary-card]
        :where(
            span,
            small,
            p,
            [class*="label"]
        ) {
            margin: 0 0 .7mm !important;
            font-size: 6.8pt !important;
            line-height: 1.12 !important;
        }

        .kes-confirmed-print-root [data-kes-print-summary-card]
        :where(
            strong,
            b,
            [class*="value"]
        ) {
            margin: 0 !important;
            font-size: 11pt !important;
            line-height: 1.05 !important;
        }

        .kes-confirmed-print-root [class*="table-wrap"],
        .kes-confirmed-print-root [class*="table-scroll"],
        .kes-confirmed-print-root [class*="table-container"],
        .kes-confirmed-print-root .overflow-x-auto,
        .kes-confirmed-print-root .overflow-auto {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            margin: 1.5mm 0 0 !important;
            overflow: visible !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .kes-confirmed-print-root table {
            display: table !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            font-size: 6.6pt !important;
        }

        .kes-confirmed-print-root thead {
            display: table-header-group !important;
        }

        .kes-confirmed-print-root tfoot {
            display: table-footer-group !important;
        }

        .kes-confirmed-print-root tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .kes-confirmed-print-root th,
        .kes-confirmed-print-root td {
            min-width: 0 !important;
            max-width: none !important;
            padding: 1.25mm 1.4mm !important;
            border-bottom: 1px solid #d8dfdb !important;
            white-space: normal !important;
            overflow: visible !important;
            overflow-wrap: anywhere !important;
            word-break: normal !important;
            text-overflow: clip !important;
            vertical-align: top !important;
        }

        .kes-confirmed-print-root th {
            background: #f3f7f5 !important;
            color: #1f372f !important;
            font-weight: 700 !important;
        }

        .kes-confirmed-print-root :where(
            .badge,
            [class*="badge"],
            [class*="status"]
        ) {
            display: inline-block !important;
            max-width: 100% !important;
            padding: .5mm 1mm !important;
            font-size: 6.1pt !important;
            line-height: 1.1 !important;
            white-space: normal !important;
        }

        .kes-confirmed-print-root[data-kes-report-kind="passport-validity"]
        table {
            font-size: 6.1pt !important;
        }

        .kes-confirmed-print-root[data-kes-report-kind="travel-insurance"]
        table {
            font-size: 6.2pt !important;
        }
    `;

    const removeExistingFrame = () => {
        document.getElementById(frameId)?.remove();
    };

    const createFrame = () => {
        removeExistingFrame();

        const frame = document.createElement('iframe');

        frame.id = frameId;
        frame.setAttribute(
            'title',
            'Report print preview',
        );
        frame.setAttribute('aria-hidden', 'true');

        Object.assign(frame.style, {
            position: 'fixed',
            right: '0',
            bottom: '0',
            width: '1px',
            height: '1px',
            border: '0',
            opacity: '0',
            pointerEvents: 'none',
        });

        document.body.appendChild(frame);

        return frame;
    };

    const directChildren = (element) => Array.from(
        element.children,
    ).filter((child) => ! child.matches(
        'script,style,template,link',
    ));

    const annotateSummaryGrid = (root) => {
        const candidates = Array.from(
            root.querySelectorAll(
                '[class*="stats"],'
                + '[class*="summary"],'
                + '[class*="metrics"],'
                + '[class*="kpi"],'
                + '[class*="grid"]',
            ),
        );

        for (const candidate of candidates) {
            if (
                candidate.matches(
                    'form,[class*="filter"],[class*="button"]',
                )
                || candidate.querySelector('table')
            ) {
                continue;
            }

            const children = directChildren(candidate);

            if (
                children.length < 3
                || children.length > 8
            ) {
                continue;
            }

            const meaningfulCards = children.filter((child) => {
                const text = normalizedText(child);

                return text.length > 0
                    && text.length < 250;
            });

            if (meaningfulCards.length < 3) {
                continue;
            }

            candidate.setAttribute(
                'data-kes-print-summary-grid',
                '1',
            );

            meaningfulCards.forEach((card) => {
                card.setAttribute(
                    'data-kes-print-summary-card',
                    '1',
                );
            });

            return;
        }
    };

    const cleanClone = (clone, config) => {
        clone.classList.add(
            'kes-confirmed-print-root',
        );
        clone.setAttribute(
            'data-kes-report-kind',
            config.key,
        );

        clone.querySelectorAll(
            'form,'
            + 'nav,'
            + 'aside,'
            + 'button,'
            + 'script,'
            + 'style,'
            + 'link,'
            + '.no-print,'
            + '[class*="no-print"],'
            + '[class*="-no-print"],'
            + '[data-karwan-report-primary-actions],'
            + '[data-karwan-export-actions],'
            + '.kes-report-export-actions,'
            + '.kes-print-action,'
            + '.fi-header-actions,'
            + '.fi-breadcrumbs,'
            + '.fi-pagination,'
            + '.fi-ta-actions,'
            + '.fi-ta-header-toolbar,'
            + '.karwan-ai-widget,'
            + '[class*="karwan-ai"]',
        ).forEach((element) => {
            element.remove();
        });

        annotateSummaryGrid(clone);

        clone.querySelectorAll('table').forEach((table) => {
            table.setAttribute(
                'data-kes-print-table',
                '1',
            );
        });

        return clone;
    };

    const resolveSource = () => document.querySelector(
        '.fi-page-content',
    )
        || document.querySelector('.fi-page')
        || document.querySelector('main');

    const waitForDocument = async (printDocument) => {
        if (printDocument.fonts?.ready) {
            try {
                await printDocument.fonts.ready;
            } catch (error) {
                console.warn(
                    'Report print font readiness failed.',
                    error,
                );
            }
        }

        await new Promise((resolve) => {
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(resolve);
            });
        });
    };

    const cleanupFrame = (frame) => {
        window.setTimeout(() => {
            frame.remove();
        }, 250);
    };

    const openConfirmedReportPrint = async (config) => {
        const source = resolveSource();

        if (! source) {
            console.error(
                'Printable report source was not found.',
            );

            return false;
        }

        const frame = createFrame();
        const printWindow = frame.contentWindow;
        const printDocument = frame.contentDocument;

        if (! printWindow || ! printDocument) {
            frame.remove();

            console.error(
                'Report print iframe could not be created.',
            );

            return false;
        }

        printDocument.open();
        printDocument.write(
            '<!doctype html>'
            + '<html lang="en">'
            + '<head>'
            + '<meta charset="utf-8">'
            + '<meta name="viewport" '
            + 'content="width=device-width,initial-scale=1">'
            + `<title>${config.title} - Print Preview</title>`
            + '</head>'
            + '<body></body>'
            + '</html>',
        );
        printDocument.close();

        const style = printDocument.createElement('style');

        style.id =
            'karwan-confirmed-report-print-style';
        style.textContent = printCss;

        printDocument.head.appendChild(style);

        const clone = cleanClone(
            source.cloneNode(true),
            config,
        );

        printDocument.body.appendChild(
            printDocument.importNode(
                clone,
                true,
            ),
        );

        await waitForDocument(printDocument);

        printWindow.addEventListener(
            'afterprint',
            () => {
                cleanupFrame(frame);
            },
            { once: true },
        );

        printWindow.focus();
        printWindow.print();

        window.setTimeout(() => {
            if (document.body.contains(frame)) {
                cleanupFrame(frame);
            }
        }, 60000);

        return false;
    };

    window.kesConfirmedReportPrint =
        openConfirmedReportPrint;

    document.addEventListener(
        'click',
        (event) => {
            const config = reportConfigs.get(
                window.location.pathname,
            );

            if (! config) {
                return;
            }

            const control = event.target.closest(
                'button,a',
            );

            if (! isPrintControl(control)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            void openConfirmedReportPrint(config);
        },
        true,
    );
})();
/* PHASE 16F-3D9 THREE CONFIRMED REPORT PRINT FIXES END */

/* PHASE 16F-3D13 FINAL FOUR REPORT RUNTIME START */
(() => {
    'use strict';

    const normalizePath = (path) => {
        const normalized = String(path || '/')
            .replace(/\/+$/, '');

        return normalized || '/';
    };

    const configs = new Map([
        [
            '/admin/customer-advance-balance-report',
            {
                key: 'customer-advance',
                title: 'Customer Advance Balance Report',
                route:
                    'filament.admin.pages.customer-advance-balance-report',
                selector: '.advance-report',
                visibleExports: false,
            },
        ],
        [
            '/admin/customer-aging-report',
            {
                key: 'customer-aging',
                title: 'Customer Aging Report',
                route:
                    'filament.admin.pages.customer-aging-report',
                selector: '.aging-page',
                visibleExports: false,
            },
        ],
        [
            '/admin/error-log-viewer',
            {
                key: 'error-log',
                title: 'Error Log Viewer',
                route:
                    'filament.admin.pages.error-log-viewer',
                selector: '.kes-error-log-report',
                visibleExports: true,
            },
        ],
        [
            '/admin/expiring-documents-report',
            {
                key: 'expiring-documents',
                title: 'Expiring Documents Report',
                route:
                    'filament.admin.pages.expiring-documents-report',
                selector:
                    '.kes-expiring-documents-report',
                visibleExports: true,
            },
        ],
    ]);

    const frameId = 'kes-final-four-report-print-frame';

    const currentConfig = () => configs.get(
        normalizePath(window.location.pathname),
    );

    const normalizedText = (element) => String(
        element?.textContent || '',
    )
        .replace(/\s+/g, ' ')
        .trim();

    const actionType = (control) => {
        if (! control) {
            return null;
        }

        if (
            control.hasAttribute(
                'data-kes-final-four-print',
            )
            || control.hasAttribute(
                'data-karwan-primary-print',
            )
        ) {
            return 'print';
        }

        const explicit = String(
            control.dataset.karwanExportAction
            || control.dataset.reportAction
            || '',
        )
            .trim()
            .toLowerCase();

        if (
            explicit === 'print'
            || explicit === 'pdf'
            || explicit === 'excel'
        ) {
            return explicit;
        }

        const onclick = String(
            control.getAttribute('onclick') || '',
        ).toLowerCase();

        for (const type of ['print', 'pdf', 'excel']) {
            if (
                onclick.includes(
                    `kesreportdownload('${type}')`,
                )
                || onclick.includes(
                    `kesreportdownload("${type}")`,
                )
            ) {
                return type;
            }
        }

        if (onclick.includes('window.print')) {
            return 'print';
        }

        const text = normalizedText(control).toLowerCase();

        if (
            text === 'print'
            || text === 'print report'
            || text === 'print preview'
        ) {
            return 'print';
        }

        if (
            text === 'pdf'
            || text === 'download pdf'
            || text === 'pdf download'
        ) {
            return 'pdf';
        }

        if (
            text === 'excel'
            || text === 'download excel'
            || text === 'excel export'
        ) {
            return 'excel';
        }

        return null;
    };

    const printCss = `
        @page {
            size: A4 landscape;
            margin: 6mm;
        }

        @page kesFinalFourLandscape {
            size: A4 landscape;
            margin: 6mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 297mm;
            min-width: 297mm;
            max-width: 297mm;
            min-height: 210mm;
            margin: 0;
            padding: 0;
            page: kesFinalFourLandscape;
            color: #111;
            background: #fff;
            font-family:
                Inter,
                "Segoe UI",
                Arial,
                sans-serif;
        }

        .kes-final-four-print-heading {
            margin: 0 0 2.5mm;
            color: #153f32;
            font-size: 15pt;
            line-height: 1.15;
        }

        .kes-final-four-print-root {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            page: kesFinalFourLandscape;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            color: #111 !important;
            background: #fff !important;
        }

        .kes-final-four-print-root :where(
            form,
            nav,
            aside,
            button,
            input,
            select,
            textarea,
            script,
            style,
            link,
            .no-print,
            [class*="no-print"],
            [class*="-no-print"],
            [data-karwan-report-primary-actions],
            [data-karwan-export-actions],
            .kes-report-export-actions,
            .kes-print-action,
            .fi-header-actions,
            .fi-breadcrumbs,
            .fi-pagination,
            .fi-ta-actions,
            .fi-ta-header-toolbar,
            .advance-filters,
            .advance-buttons,
            .aging-filters,
            .aging-buttons,
            .karwan-ai-widget,
            [class*="karwan-ai"]
        ) {
            display: none !important;
        }

        .kes-final-four-print-root > *,
        .kes-final-four-print-root section,
        .kes-final-four-print-root article,
        .kes-final-four-print-root [class*="panel"],
        .kes-final-four-print-root [class*="card"],
        .kes-final-four-print-root [class*="wrap"] {
            min-width: 0 !important;
            max-width: 100% !important;
        }

        .kes-final-four-print-root h1,
        .kes-final-four-print-root h2 {
            margin: 0 0 1.4mm !important;
            font-size: 12pt !important;
            line-height: 1.15 !important;
        }

        .kes-final-four-print-root h3 {
            margin: 0 0 1mm !important;
            font-size: 10pt !important;
            line-height: 1.15 !important;
        }

        .kes-final-four-print-root p {
            margin: 0 0 1.4mm !important;
            font-size: 7.2pt !important;
            line-height: 1.25 !important;
        }

        .kes-final-four-print-root [data-kes-final-summary-grid] {
            display: grid !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            grid-template-columns:
                repeat(4, minmax(0, 1fr)) !important;
            grid-auto-flow: row !important;
            gap: 2mm !important;
            margin: 0 0 2.5mm !important;
            align-items: stretch !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="expiring-documents"
        ] [data-kes-final-summary-grid] {
            grid-template-columns:
                repeat(3, minmax(0, 1fr)) !important;
        }

        .kes-final-four-print-root [data-kes-final-summary-card] {
            display: block !important;
            width: auto !important;
            min-width: 0 !important;
            min-height: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 2.2mm 2.5mm !important;
            border: 1px solid #d8dfdb !important;
            border-radius: 2mm !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            background: #fff !important;
        }

        .kes-final-four-print-root [data-kes-final-summary-card]
        :where(
            span,
            small,
            p,
            [class*="label"]
        ) {
            margin: 0 0 .6mm !important;
            font-size: 6.5pt !important;
            line-height: 1.12 !important;
        }

        .kes-final-four-print-root [data-kes-final-summary-card]
        :where(
            strong,
            b,
            [class*="value"]
        ) {
            margin: 0 !important;
            font-size: 10.5pt !important;
            line-height: 1.05 !important;
        }

        .kes-final-four-print-root [class*="table-wrap"],
        .kes-final-four-print-root [class*="table-scroll"],
        .kes-final-four-print-root [class*="table-container"],
        .kes-final-four-print-root .overflow-x-auto,
        .kes-final-four-print-root .overflow-auto,
        .kes-final-four-print-root .overflow-hidden {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            height: auto !important;
            max-height: none !important;
            margin: 1.2mm 0 0 !important;
            overflow: visible !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .kes-final-four-print-root table {
            display: table !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            table-layout: auto !important;
            border-collapse: collapse !important;
            font-size: 6.1pt !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="customer-advance"
        ] table,
        .kes-final-four-print-root[
            data-kes-final-report-kind="customer-aging"
        ] table {
            font-size: 5.7pt !important;
        }

        .kes-final-four-print-root thead {
            display: table-header-group !important;
        }

        .kes-final-four-print-root tfoot {
            display: table-footer-group !important;
        }

        .kes-final-four-print-root tr {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .kes-final-four-print-root th,
        .kes-final-four-print-root td {
            min-width: 0 !important;
            max-width: none !important;
            padding: 1.15mm 1.25mm !important;
            border-bottom: 1px solid #d8dfdb !important;
            white-space: normal !important;
            overflow: visible !important;
            overflow-wrap: anywhere !important;
            word-break: normal !important;
            text-overflow: clip !important;
            vertical-align: top !important;
        }

        .kes-final-four-print-root th {
            background: #f3f7f5 !important;
            color: #1f372f !important;
            font-weight: 700 !important;
        }

        .kes-final-four-print-root :where(
            .badge,
            [class*="badge"],
            [class*="status"]
        ) {
            display: inline-block !important;
            max-width: 100% !important;
            padding: .45mm .9mm !important;
            font-size: 5.9pt !important;
            line-height: 1.1 !important;
            white-space: normal !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="customer-advance"
        ] [data-kes-final-summary-grid] {
            grid-template-columns:
                repeat(4, minmax(0, 1fr)) !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] table {
            table-layout: fixed !important;
            font-size: 5.2pt !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th,
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td {
            padding: .9mm 1mm !important;
            line-height: 1.2 !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(1),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(1) {
            width: 6% !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(2),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(2) {
            width: 39% !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(3),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(3) {
            width: 25% !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(4),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(4) {
            width: 7% !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(5),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(5) {
            width: 9% !important;
        }

        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(6),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(6),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] th:nth-child(7),
        .kes-final-four-print-root[
            data-kes-final-report-kind="error-log"
        ] td:nth-child(7) {
            width: 7% !important;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            @page kesFinalFourLandscape {
                size: A4 landscape;
                margin: 6mm;
            }

            html,
            body {
                width: 297mm !important;
                min-width: 297mm !important;
                max-width: 297mm !important;
                min-height: 210mm !important;
                page: kesFinalFourLandscape;
            }
        }

        .kes-final-four-print-root pre {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            max-height: none !important;
            margin: 2mm 0 0 !important;
            padding: 2.5mm !important;
            overflow: visible !important;
            border: 1px solid #cbd5d1 !important;
            border-radius: 1.5mm !important;
            color: #111 !important;
            background: #f7faf8 !important;
            font-family:
                Consolas,
                "Courier New",
                monospace !important;
            font-size: 5.7pt !important;
            line-height: 1.3 !important;
            white-space: pre-wrap !important;
            overflow-wrap: anywhere !important;
            word-break: break-word !important;
            break-inside: auto !important;
            page-break-inside: auto !important;
        }
    `;

    const directChildren = (element) => Array.from(
        element.children,
    ).filter((child) => ! child.matches(
        'script,style,template,link',
    ));

    const annotateSummaryGrid = (root) => {
        const preferred = Array.from(
            root.querySelectorAll(
                '.advance-stats,'
                + '.advance-summary,'
                + '.advance-summary-grid,'
                + '.aging-stats,'
                + '[class*="summary-grid"],'
                + '[class*="stats-grid"],'
                + '[class*="metric-grid"],'
                + '[class*="kpi-grid"],'
                + '.grid',
            ),
        );

        for (const candidate of preferred) {
            if (
                candidate.matches(
                    'form,[class*="filter"],[class*="button"]',
                )
                || candidate.querySelector('table')
            ) {
                continue;
            }

            const children = directChildren(candidate);
            const meaningful = children.filter((child) => {
                const text = normalizedText(child);

                return text.length > 0
                    && text.length < 350;
            });

            if (
                meaningful.length < 3
                || meaningful.length > 8
            ) {
                continue;
            }

            candidate.setAttribute(
                'data-kes-final-summary-grid',
                '1',
            );

            meaningful.forEach((card) => {
                card.setAttribute(
                    'data-kes-final-summary-card',
                    '1',
                );
            });

            return;
        }
    };

    const removeNonReportContent = (root) => {
        root.querySelectorAll(
            'form,'
            + 'nav,'
            + 'aside,'
            + 'button,'
            + 'input,'
            + 'select,'
            + 'textarea,'
            + 'script,'
            + 'style,'
            + 'link,'
            + '.no-print,'
            + '[class*="no-print"],'
            + '[class*="-no-print"],'
            + '[data-karwan-report-primary-actions],'
            + '[data-karwan-export-actions],'
            + '.kes-report-export-actions,'
            + '.kes-print-action,'
            + '.fi-header-actions,'
            + '.fi-breadcrumbs,'
            + '.fi-pagination,'
            + '.fi-ta-actions,'
            + '.fi-ta-header-toolbar,'
            + '.advance-filters,'
            + '.advance-buttons,'
            + '.aging-filters,'
            + '.aging-buttons,'
            + '.karwan-ai-widget,'
            + '[class*="karwan-ai"]',
        ).forEach((element) => {
            element.remove();
        });
    };

    const truncateRawPreviews = (root) => {
        root.querySelectorAll('pre').forEach((pre) => {
            const value = String(pre.textContent || '');
            const limit = 120000;

            if (value.length <= limit) {
                return;
            }

            pre.textContent =
                value.slice(0, limit)
                + '\n\n[Raw log preview truncated for safe export.]';
        });
    };

    /* PHASE 16F-3D14 FINAL FOUR PRINT COMPOSITION V3 START */
    const markSummaryGrid = (grid) => {
        if (! grid) {
            return;
        }

        const cards = directChildren(grid).filter((card) => {
            const text = normalizedText(card);

            return text.length > 0
                && text.length < 500;
        });

        if (cards.length < 3) {
            return;
        }

        grid.setAttribute(
            'data-kes-final-summary-grid',
            '1',
        );

        cards.forEach((card) => {
            card.setAttribute(
                'data-kes-final-summary-card',
                '1',
            );
        });
    };

    const annotateKnownSummaryGrid = (
        root,
        config,
    ) => {
        if (config.key === 'customer-advance') {
            markSummaryGrid(
                root.querySelector('.advance-cards'),
            );

            return;
        }

        if (config.key === 'customer-aging') {
            markSummaryGrid(
                root.querySelector('.aging-stats'),
            );
        }
    };

    const prepareErrorLogPrint = (
        root,
        config,
        mode,
    ) => {
        if (
            config.key !== 'error-log'
            || mode !== 'print'
        ) {
            return;
        }

        /*
         * Filter controls leave their labels behind after generic cleanup.
         * Remove the complete filter card before controls are stripped.
         */
        root.querySelectorAll(
            'input,select,textarea',
        ).forEach((control) => {
            const card = control.closest(
                '.rounded-xl',
            );

            if (card && ! card.querySelector('table')) {
                card.remove();
            }
        });

        /*
         * Raw log text can span many sheets and is already available in the
         * interactive viewer. The printable incident report keeps summary
         * cards and structured incidents only.
         */
        root.querySelectorAll('pre').forEach((pre) => {
            const card = pre.closest(
                '.rounded-xl',
            ) || pre.parentElement;

            card?.remove();
        });

        const table = root.querySelector('table');

        if (! table) {
            return;
        }

        /*
         * The last column only contains the interactive Open action.
         * Removing that UI-only column gives the diagnostic fields usable
         * print width without dropping report data.
         */
        table.querySelectorAll(
            'tr > :last-child',
        ).forEach((cell) => {
            cell.remove();
        });
    };
    /* PHASE 16F-3D14 FINAL FOUR PRINT COMPOSITION V3 END */

    const prepareClone = (
        source,
        config,
        mode = 'export',
    ) => {
        const clone = source.cloneNode(true);

        clone.classList.add(
            'kes-final-four-print-root',
        );
        clone.setAttribute(
            'data-kes-final-report-kind',
            config.key,
        );

        prepareErrorLogPrint(
            clone,
            config,
            mode,
        );
        removeNonReportContent(clone);

        if (mode !== 'print') {
            truncateRawPreviews(clone);
        }

        annotateKnownSummaryGrid(
            clone,
            config,
        );
        annotateSummaryGrid(clone);

        return clone;
    };

    const removeExistingFrame = () => {
        document.getElementById(frameId)?.remove();
    };

    const createFrame = () => {
        removeExistingFrame();

        const frame = document.createElement('iframe');

        frame.id = frameId;
        frame.setAttribute(
            'title',
            'Report print preview',
        );
        frame.setAttribute('aria-hidden', 'true');

        Object.assign(frame.style, {
            position: 'fixed',
            left: '-12000px',
            top: '0',
            width: '297mm',
            height: '210mm',
            border: '0',
            opacity: '0',
            visibility: 'hidden',
            pointerEvents: 'none',
        });

        document.body.appendChild(frame);

        return frame;
    };

    const waitForDocument = async (printDocument) => {
        if (printDocument.fonts?.ready) {
            try {
                await printDocument.fonts.ready;
            } catch (error) {
                console.warn(
                    'Final report print font readiness failed.',
                    error,
                );
            }
        }

        await new Promise((resolve) => {
            window.requestAnimationFrame(() => {
                window.requestAnimationFrame(resolve);
            });
        });
    };

    const cleanupFrame = (frame) => {
        window.setTimeout(() => {
            frame.remove();
        }, 250);
    };

    const openPrintPreview = async (config) => {
        const source = document.querySelector(
            config.selector,
        );

        if (! source) {
            window.alert(
                'Printable report section was not found.',
            );

            return false;
        }

        const frame = createFrame();
        const printWindow = frame.contentWindow;
        const printDocument = frame.contentDocument;

        if (! printWindow || ! printDocument) {
            frame.remove();

            window.alert(
                'Print Preview frame could not be created.',
            );

            return false;
        }

        printDocument.open();
        printDocument.write(
            '<!doctype html>'
            + '<html lang="en">'
            + '<head>'
            + '<meta charset="utf-8">'
            + '<meta name="viewport" '
            + 'content="width=device-width,initial-scale=1">'
            + `<title>${config.title} - Print Preview</title>`
            + '</head>'
            + '<body></body>'
            + '</html>',
        );
        printDocument.close();

        const style = printDocument.createElement('style');

        style.id =
            'karwan-final-four-report-print-style';
        style.textContent = printCss;
        printDocument.head.appendChild(style);

        const heading = printDocument.createElement('h1');

        heading.className =
            'kes-final-four-print-heading';
        heading.textContent = config.title;
        printDocument.body.appendChild(heading);

        const clone = prepareClone(
            source,
            config,
            'print',
        );

        printDocument.body.appendChild(
            printDocument.importNode(
                clone,
                true,
            ),
        );

        await waitForDocument(printDocument);

        printWindow.addEventListener(
            'afterprint',
            () => {
                cleanupFrame(frame);
            },
            { once: true },
        );

        printWindow.focus();
        printWindow.print();

        window.setTimeout(() => {
            if (document.body.contains(frame)) {
                cleanupFrame(frame);
            }
        }, 60000);

        return false;
    };

    const exportEndpoint = (format) => {
        const template = document.querySelector(
            'meta[name="karwan-report-export-url"]',
        )?.content?.trim() || '';

        if (! template) {
            return '';
        }

        return template.replace(
            '__FORMAT__',
            encodeURIComponent(format),
        );
    };

    const csrfToken = () =>
        document.querySelector(
            'meta[name="karwan-report-csrf-token"]',
        )?.content?.trim()
        || document.querySelector(
            'meta[name="csrf-token"]',
        )?.content?.trim()
        || '';

    const tableTitle = (table, index) => {
        let current = table.previousElementSibling;

        while (current) {
            if (
                current.matches('h1,h2,h3,h4')
                && normalizedText(current)
            ) {
                return normalizedText(current);
            }

            current = current.previousElementSibling;
        }

        const section = table.closest(
            'section,article,[class*="panel"],[class*="card"]',
        );

        const heading = section?.querySelector(
            'h1,h2,h3,h4',
        );

        return normalizedText(heading)
            || `Table ${index + 1}`;
    };

    const tablePayload = (root) => Array.from(
        root.querySelectorAll('table'),
    )
        .slice(0, 30)
        .map((table, index) => {
            const headers = Array.from(
                table.querySelectorAll('thead th'),
            )
                .slice(0, 100)
                .map(normalizedText);

            const rows = Array.from(
                table.querySelectorAll('tbody tr'),
            )
                .slice(0, 10000)
                .map((row) => Array.from(
                    row.querySelectorAll('th,td'),
                )
                    .slice(0, 100)
                    .map(normalizedText));

            return {
                title: tableTitle(table, index),
                headers,
                rows,
            };
        });

    const textPayload = (root) => {
        const rows = [];
        const seen = new Set();

        root.querySelectorAll(
            'h1,h2,h3,h4,p,li,dt,dd',
        ).forEach((element) => {
            if (
                element.closest('table')
                || element.closest(
                    '[data-karwan-export-actions]',
                )
            ) {
                return;
            }

            const text = normalizedText(element)
                .slice(0, 3000);

            if (
                ! text
                || seen.has(text)
                || rows.length >= 1000
            ) {
                return;
            }

            seen.add(text);
            rows.push(text);
        });

        return rows;
    };

    const responseFilename = (
        response,
        fallback,
    ) => {
        const header = response.headers.get(
            'Content-Disposition',
        ) || '';

        const utf8 = header.match(
            /filename\*=UTF-8''([^;]+)/i,
        );

        if (utf8?.[1]) {
            try {
                return decodeURIComponent(
                    utf8[1].replace(/["']/g, ''),
                );
            } catch {
                return utf8[1].replace(/["']/g, '');
            }
        }

        const plain = header.match(
            /filename="?([^";]+)"?/i,
        );

        return plain?.[1] || fallback;
    };

    const downloadBlob = (blob, filename) => {
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');

        anchor.href = url;
        anchor.download = filename;
        anchor.style.display = 'none';

        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();

        window.setTimeout(() => {
            URL.revokeObjectURL(url);
        }, 1500);
    };

    const exportVisible = async (
        config,
        format,
        button,
    ) => {
        const source = document.querySelector(
            config.selector,
        );
        const endpoint = exportEndpoint(format);
        const token = csrfToken();

        if (! source || ! endpoint || ! token) {
            window.alert(
                'Report export configuration is incomplete.',
            );

            return false;
        }

        const clone = prepareClone(source, config);
        const html = clone.innerHTML;

        if (html.length > 2400000) {
            window.alert(
                'Rendered report is too large to export safely.',
            );

            return false;
        }

        const payload = {
            title: config.title,
            source_url: window.location.href,
            source_route: config.route,
            html,
            tables: tablePayload(source),
            text_rows: textPayload(clone),
        };

        const originalHtml = button.innerHTML;

        button.disabled = true;
        button.classList.add('is-loading');
        button.innerHTML = '<span>Preparing…</span>';

        try {
            const response = await fetch(
                endpoint,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        Accept:
                            'application/octet-stream',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                },
            );

            if (! response.ok) {
                let message =
                    `Export failed (${response.status}).`;

                try {
                    const error = await response.json();

                    message =
                        error.message || message;
                } catch {
                    // Non-JSON error response.
                }

                throw new Error(message);
            }

            const blob = await response.blob();
            const fallback =
                `${config.key}.${format === 'pdf'
                    ? 'pdf'
                    : 'xls'}`;

            downloadBlob(
                blob,
                responseFilename(
                    response,
                    fallback,
                ),
            );
        } catch (error) {
            console.error(
                'Final four report export failed:',
                error,
            );

            window.alert(
                error instanceof Error
                    ? error.message
                    : 'Report export failed.',
            );
        } finally {
            button.disabled = false;
            button.classList.remove('is-loading');
            button.innerHTML = originalHtml;
        }

        return false;
    };

    window.kesFinalFourReportPrint =
        openPrintPreview;
    window.kesFinalFourVisibleExport =
        exportVisible;

    document.addEventListener(
        'click',
        (event) => {
            const config = currentConfig();

            if (! config) {
                return;
            }

            const control = event.target.closest(
                'button,a',
            );
            const type = actionType(control);

            if (! type) {
                return;
            }

            if (
                type !== 'print'
                && ! config.visibleExports
            ) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (type === 'print') {
                void openPrintPreview(config);

                return;
            }

            void exportVisible(
                config,
                type,
                control,
            );
        },
        true,
    );
})();
/* PHASE 16F-3D13 FINAL FOUR REPORT RUNTIME END */
