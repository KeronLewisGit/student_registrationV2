{{-- Shared stylesheet for the single-student and bulk print pages.
     Deliberately plain: no watermark, gradients, shadows or icons, so the
     printed record reads as a clean official document. --}}
<style>
    @page {
        size: Legal;
        margin: 15mm 12mm 13mm;
    }

    body {
        background: #e9ecef;
        padding: 2rem 1rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #111;
    }

    /* One sheet per student */
    .profile-card {
        position: relative;
        max-width: 8.5in;
        margin: 0 auto 1.5rem;
        padding: 0.6in 0.5in;
        background: #fff;
        border: 1px solid #d1d5db;
    }

    .profile-inner {
        position: relative;
    }

    /* Letterhead */
    .print-header {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        padding-bottom: 0.75rem;
        margin-bottom: 0.5rem !important;
        border-bottom: 1.5px solid #111;
    }

    .print-header .col-md-3 { flex: 0 0 22%; max-width: 22%; }
    .print-header .col-md-6 { flex: 0 0 56%; max-width: 56%; padding: 0 0.5rem; }
    .print-header .col-md-3.text-end { display: flex; justify-content: flex-end; }

    .print-header h6 {
        font-size: 0.66rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #555;
        margin-bottom: 0.3rem;
    }

    .print-header h2 {
        font-size: 1.4rem !important;
        line-height: 1.2;
        margin-bottom: 0.25rem !important;
    }

    .print-header .record-title {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #555;
        margin: 0;
    }

    .print-header .record-subject {
        font-size: 1rem;
        font-weight: 600;
        color: #111;
        margin: 0.35rem 0 0;
    }

    .passport-photo {
        width: 96px;
        height: 96px;
        object-fit: cover;
        border: 1px solid #999;
        background: #fff;
    }

    .school-logo {
        width: 100px;
        height: auto;
    }

    /* Sections: a titled block with a hairline under the title */
    .section-card {
        padding: 0;
        margin-top: 0.9rem;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .section-card .fw-bold.border-bottom {
        font-size: 0.88rem !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #111;
        border-color: #999 !important;
        border-width: 1px !important;
        padding-bottom: 0.25rem !important;
        margin-bottom: 0.45rem !important;
    }

    .section-card .fw-bold i {
        display: none; /* no icons on an official record */
    }

    .section-card-compact .fw-bold {
        font-size: 0.88rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #999;
        padding-bottom: 0.25rem;
    }

    .section-card-compact .compact-value {
        font-size: 0.8rem;
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: #111;
        margin-left: 0.75rem;
    }

    .section-card h5 {
        font-size: 0.62rem;
        font-weight: 600;
        color: #555;
        margin-bottom: 0.1rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .section-card p {
        font-size: 0.8rem;
        font-weight: 400;
        color: #111;
        margin: 0;
        padding: 0 0 0.3rem;
        line-height: 1.3;
    }

    .section-card a {
        color: inherit;
        text-decoration: none;
    }

    /* Grid: pinned to the intended columns (the sheet is narrower than
       Bootstrap's "md" breakpoint, so its responsive classes would stack). */
    .row {
        display: flex;
        flex-wrap: wrap;
        --bs-gutter-x: 0.9rem;
        --bs-gutter-y: 0;
        margin-left: calc(-0.5 * var(--bs-gutter-x));
        margin-right: calc(-0.5 * var(--bs-gutter-x));
        margin-top: 0;
    }
    .row > [class*="col-"] {
        padding-left: calc(0.5 * var(--bs-gutter-x));
        padding-right: calc(0.5 * var(--bs-gutter-x));
        margin-top: 0;
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .col-md-3  { flex: 0 0 25%;      max-width: 25%; }
    .col-md-4  { flex: 0 0 33.3333%; max-width: 33.3333%; }
    .col-md-6  { flex: 0 0 50%;      max-width: 50%; }
    .col-md-8  { flex: 0 0 66.6667%; max-width: 66.6667%; }
    .col-md-12 { flex: 0 0 100%;     max-width: 100%; }
    .row.mt-2  { margin-top: 0.3rem !important; }

    /* Footer line under each profile */
    .print-footer {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.8rem;
        padding-top: 0.3rem;
        border-top: 1px solid #999;
        font-size: 0.66rem;
        color: #555;
        break-inside: avoid;
    }

    .print-only { display: none; }

    @media print {
        html, body {
            padding: 0 !important;
            margin: 0 !important;
            background: #fff !important;
            font-size: 10.5px;
            line-height: 1.3;
        }

        .no-print { display: none !important; }
        .print-only { display: inline; }

        .profile-card {
            max-width: none;
            margin: 0;
            padding: 1mm 4mm 0 !important;
            border: 0;
        }

        .print-header {
            padding-bottom: 0.5rem;
            margin-bottom: 0.3rem !important;
        }
        .print-header h2 { font-size: 1.35rem !important; }
        .passport-photo { width: 88px; height: 88px; }
        .school-logo { width: 96px; }

        .section-card { margin-top: 0.55rem; }
        .section-card .fw-bold.border-bottom { margin-bottom: 0.35rem !important; }
        .section-card p { padding-bottom: 0.22rem; }

        .print-footer {
            margin-top: 0.5rem;
            padding-top: 0.25rem;
        }
    }
</style>
