<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #10251f;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.45;
        }

        .export-header {
            margin-bottom: 12px;
            padding-bottom: 9px;
            border-bottom: 2px solid #064937;
        }

        .export-header h1 {
            margin: 0 0 4px;
            color: #064937;
            font-size: 21px;
        }

        .export-meta {
            color: #64736e;
            font-size: 8px;
        }

        h2,
        h3,
        h4 {
            margin: 14px 0 7px;
            color: #064937;
            page-break-after: avoid;
        }

        p {
            margin: 4px 0;
        }

        table {
            width: 100%;
            margin: 8px 0 14px;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        th,
        td {
            padding: 5px 6px;
            border: 1px solid #c7d1cd;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        th {
            color: #064937;
            background: #fff5bf;
            font-weight: 700;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #fffdf3;
        }

        .grid,
        [class*="grid"] {
            display: block !important;
        }

        [class*="card"],
        [class*="panel"],
        [class*="section"] {
            margin: 7px 0;
            padding: 8px;
            border: 1px solid #d7dfdc;
            border-radius: 5px;
            page-break-inside: avoid;
        }

        svg,
        canvas {
            display: none !important;
        }

        img {
            max-width: 100%;
            max-height: 120mm;
        }

        pre,
        code {
            white-space: pre-wrap;
            word-break: break-word;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="export-header">
        <h1>{{ $title }}</h1>

        <div class="export-meta">
            Generated:
            {{ $generatedAt->format('d M Y H:i:s') }}
            · Source:
            {{ $sourceUrl }}
        </div>
    </div>

    <main>
        {!! $content !!}
    </main>
</body>
</html>
