{{-- Shared print stylesheet for the single-student and bulk print pages --}}
<style>
        @page {
            size: Letter;
            margin: 10mm;
        }

        body {
            background: white;
            padding: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-card {
            position: relative;
            padding: 2rem;
            min-height: 1000px;
        }

        /* Official Document Watermark */
        .profile-card::before {
            content: "OFFICIAL DOCUMENT";
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem;
            font-weight: 900;
            color: rgba(79, 70, 229, 0.08);
            text-transform: uppercase;
            letter-spacing: 0.5rem;
            white-space: nowrap;
            pointer-events: none;
            z-index: 1;
            user-select: none;
        }

        .profile-card::after {
            content: "";
            position: absolute;
            inset: 80px;
            background: url('{{ asset('images/OfficialDocument1.png') }}') center/contain no-repeat;
            opacity: 0.03;
            pointer-events: none;
            z-index: 0;
        }

        .profile-inner {
            position: relative;
            z-index: 1;
        }

        .passport-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #4f46e5;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .school-logo {
            width: 160px;
            height: auto;
        }

        .section-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e5e7eb;
            border-left: 4px solid #4f46e5;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            page-break-inside: avoid;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .section-card h5 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-card p {
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
            margin: 0;
            padding: 0.5rem 0;
        }

        .print-footer {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.75rem;
            color: #64748b;
        }

        .print-header .record-title {
            margin: 0;
        }

        .print-header .record-subject {
            font-weight: 600;
            color: #1e293b;
            margin: 0.5rem 0 0;
        }

        .fw-bold.border-bottom {
            border-color: #4f46e5 !important;
            border-width: 2px !important;
            padding-bottom: 0.75rem !important;
        }

        .fw-bold i {
            color: #4f46e5;
        }

        /* Print-only helpers */
        .print-only { display: none; }

        @media print {
            /* Letter with a 12 mm frame; the footer sits inside the page box. */
            @page {
                size: Letter;
                margin: 12mm 12mm 14mm;
            }

            html, body {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
                font-size: 11px;
                line-height: 1.35;
                color: #111;
            }

            .no-print { display: none !important; }
            .print-only { display: inline; }

            .profile-card {
                padding: 0 !important;
                min-height: 0 !important;
            }

            /* The print sheet (Letter minus margins) is narrower than Bootstrap's
               "md" breakpoint, so its responsive columns would stack into one
               field per line. Pin the grid to the intended layout instead. */
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

            .row.mt-2 { margin-top: 0.3rem !important; }

            /* Letterhead */
            .print-header {
                display: flex;
                flex-wrap: nowrap;
                align-items: center;
                padding-bottom: 0.6rem;
                margin-bottom: 0.4rem !important;
                border-bottom: 2px solid #4f46e5;
                break-inside: avoid;
            }
            .print-header .col-md-3 { flex: 0 0 22%; max-width: 22%; }
            .print-header .col-md-6 { flex: 0 0 56%; max-width: 56%; padding: 0 0.5rem; }
            .print-header .col-md-3.text-end { display: flex; justify-content: flex-end; }
            .print-header h6 {
                font-size: 0.68rem;
                text-transform: uppercase;
                letter-spacing: 0.4px;
                color: #64748b;
                margin-bottom: 0.3rem;
            }
            .print-header h2 {
                font-size: 1.45rem !important;
                line-height: 1.2;
                margin-bottom: 0.25rem !important;
            }
            .print-header .record-title {
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #64748b;
                margin: 0;
            }
            .print-header .record-subject {
                font-size: 1rem;
                font-weight: 600;
                color: #1e293b;
                margin: 0.35rem 0 0;
            }
            .passport-photo {
                width: 105px;
                height: 105px;
                border-radius: 8px;
                border-width: 2px;
                box-shadow: none;
            }
            .school-logo { width: 115px; }

            /* Sections */
            .section-card {
                background: transparent; /* let the watermark show through */
                border: 1px solid #d1d5db;
                border-left: 3px solid #4f46e5;
                border-radius: 6px;
                padding: 0.6rem 0.85rem 0.45rem;
                margin-top: 0.55rem;
                box-shadow: none;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .section-card .fw-bold.border-bottom {
                font-size: 0.9rem !important;
                padding-bottom: 0.3rem !important;
                margin-bottom: 0.45rem !important;
                border-width: 1px !important;
            }
            .section-card h5 {
                font-size: 0.62rem;
                letter-spacing: 0.4px;
                margin-bottom: 0.1rem;
            }
            .section-card p {
                font-size: 0.82rem;
                padding: 0 0 0.4rem;
                line-height: 1.3;
            }
            .section-card a {
                color: inherit;
                text-decoration: none;
            }

            /* Footer line under each profile */
            .print-footer {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                margin-top: 0.7rem;
                padding-top: 0.35rem;
                border-top: 1px solid #d1d5db;
                font-size: 0.66rem;
                color: #64748b;
                break-inside: avoid;
            }

            /* Watermarks */
            .profile-card::before {
                position: fixed;
                font-size: 4.5rem;
                opacity: 1;
                color: rgba(79, 70, 229, 0.06);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .profile-card::after { inset: 40px; }
        }
</style>
