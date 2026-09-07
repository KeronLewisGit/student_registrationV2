<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $printable['name'] }} - {{ $scope }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: @yield('page-size', 'Letter'); margin: 12mm 10mm; }

        body {
            background: #e9ecef;
            padding: 2rem 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #111;
        }
        .sheet {
            max-width: 11in;
            margin: 0 auto 1.5rem;
            padding: 0.5in;
            background: #fff;
            border: 1px solid #d1d5db;
        }
        .letterhead {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1.5px solid #111;
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .letterhead h1 { font-size: 1.25rem; font-weight: 700; margin: 0; }
        .letterhead .sub { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .letterhead .scope { font-size: 1rem; font-weight: 600; margin-top: 0.25rem; }
        .letterhead img { width: 80px; height: auto; }

        table.sheet-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        table.sheet-table th, table.sheet-table td {
            border: 1px solid #999;
            padding: 0.25rem 0.4rem;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        table.sheet-table th {
            background: #f3f4f6;
            font-size: 0.66rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #333;
            text-align: left;
            overflow-wrap: normal;   /* never split a heading mid-word */
            word-break: keep-all;
            hyphens: none;
        }
        td.phone { white-space: nowrap; }
        table.sheet-table tr { break-inside: avoid; page-break-inside: avoid; }
        table.sheet-table thead { display: table-header-group; }
        td.num { width: 2.2rem; text-align: right; color: #555; }
        td.blank { min-width: 3rem; }
        .muted { color: #777; font-style: italic; }
        .alert-row td { background: #fff7ed; }
        h2.group { font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; margin: 0.9rem 0 0.35rem; break-after: avoid; }
        .sheet-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding-top: 0.3rem;
            border-top: 1px solid #999;
            font-size: 0.66rem;
            color: #555;
        }
        .print-toolbar {
            position: sticky; top: 0; z-index: 10;
            background: #fff; border-bottom: 1px solid #d1d5db;
            padding: 0.75rem 1rem; margin: -2rem -1rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        }

        @media print {
            html, body { background: #fff !important; padding: 0 !important; margin: 0 !important; font-size: 10.5px; }
            .no-print { display: none !important; }
            .sheet { max-width: none; margin: 0; padding: 0; border: 0; }
            table.sheet-table th { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .alert-row td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="print-toolbar no-print">
        <div>
            <strong>{{ $printable['name'] }}</strong>
            <span class="text-muted ms-2">{{ $scope }} &middot; {{ $students->count() }} {{ $students->count() === 1 ? 'student' : 'students' }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('printables.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Printables</a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Print</button>
        </div>
    </div>

    <div class="sheet">
        <div class="letterhead">
            <div>
                <h1>Success Laventille Secondary School</h1>
                <div class="sub">{{ $printable['name'] }} &middot; {{ $academicYear }}</div>
                <div class="scope">{{ $scope }}</div>
            </div>
            <img src="{{ asset('images/successlogo.png') }}" alt="SLSS crest">
        </div>

        @yield('sheet')

        <div class="sheet-footer">
            <span>Success Laventille Secondary School &middot; {{ $printable['name'] }}</span>
            <span>{{ $scope }} &middot; {{ $students->count() }} students</span>
            <span>Printed {{ now()->format('d/m/Y') }}</span>
        </div>
    </div>
</body>
</html>
