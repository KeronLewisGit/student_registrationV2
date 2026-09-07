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

        .fw-bold.border-bottom {
            border-color: #4f46e5 !important;
            border-width: 2px !important;
            padding-bottom: 0.75rem !important;
        }

        .fw-bold i {
            color: #4f46e5;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .profile-card {
                padding-top: 140px !important;
            }
            .profile-card::before {
                position: fixed;
                font-size: 4.5rem;
                opacity: 1;
                color: rgba(79, 70, 229, 0.06);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .section-card {
                box-shadow: none;
                border-left-width: 3px;
                page-break-inside: avoid;
            }
        }
</style>
