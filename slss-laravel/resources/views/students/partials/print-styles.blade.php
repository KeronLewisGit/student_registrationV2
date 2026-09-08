{{-- The ONE stylesheet for the printed student record. It is rendered by the
     browser (print view, bulk print) and by dompdf (PDF download), so it uses
     only what both understand: tables, floats, px units, @page, position:fixed.
     No flexbox, no CSS variables, no Bootstrap classes.

     body.preview  = on-screen preview chrome (grey background, paper sheet)
     body.pdf      = dompdf output (no preview chrome) --}}
<style>
    @page { size: legal; margin: 15mm 12mm 13mm; }

    body {
        margin: 0;
        padding: 0;
        font-family: "DejaVu Sans", "Segoe UI", Tahoma, Arial, sans-serif;
        font-size: 10.5px;
        line-height: 1.3;
        color: #111;
        background: #fff;
    }
    * { box-sizing: border-box; }
    table { border-collapse: collapse; width: 100%; }
    td { vertical-align: top; }
    img { max-width: 100%; }

    /* ---- one sheet per student ---- */
    .profile-card { position: relative; padding: 1mm 4mm 0; }

    /* Faint diagonal watermark, well below the contrast of any text */
    .watermark {
        position: fixed;
        top: 46%;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 58px;
        font-weight: bold;
        letter-spacing: 6px;
        color: #f0f0f0;
        z-index: -1;
        transform: rotate(-32deg);
    }

    /* ---- letterhead ---- */
    .letterhead { border-bottom: 1.5px solid #111; margin-bottom: 4px; }
    .letterhead td { padding: 0 0 6px; vertical-align: middle; }
    .letterhead .photo-cell { width: 22%; }
    .letterhead .title-cell { width: 56%; text-align: center; padding: 0 8px 6px; }
    .letterhead .crest-cell { width: 22%; text-align: right; }
    .letterhead .label {
        font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px;
        color: #555; margin: 0 0 3px;
    }
    .passport-photo { width: 88px; height: 88px; border: 1px solid #999; background: #fff; object-fit: cover; }
    .school-logo { width: 96px; height: auto; }
    .letterhead h2 { font-size: 15px; line-height: 1.2; margin: 0 0 2px; font-weight: bold; }
    .record-title { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #555; margin: 0; }
    .record-subject { font-size: 10.5px; font-weight: bold; color: #111; margin: 4px 0 0; }

    /* ---- sections ---- */
    .section-card { margin-top: 6px; page-break-inside: avoid; }
    .section-title {
        font-size: 9.2px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #111;
        border-bottom: 1px solid #999; padding-bottom: 2.5px; margin-bottom: 3.5px;
    }
    .section-title .compact-value {
        font-weight: normal; text-transform: none; letter-spacing: 0; margin-left: 8px; font-size: 8.4px;
    }
    .grid td { padding: 1px 4px 3px 0; width: 25%; }
    .grid td.span-2 { width: 50%; }
    .grid td.span-3 { width: 75%; }
    .grid td.span-4 { width: 100%; }
    .grid h5 {
        font-size: 6.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px;
        color: #555; margin: 0 0 1px;
    }
    .grid p { font-size: 8.4px; color: #111; margin: 0; padding: 0 0 2.5px; line-height: 1.3; word-wrap: break-word; }
    .not-recorded { color: #777; font-style: italic; }
    a { color: inherit; text-decoration: none; }

    /* ---- footer ---- */
    .print-footer { border-top: 1px solid #999; margin-top: 8px; padding-top: 3px; font-size: 7px; color: #555; }
    .print-footer td { padding: 0; }
    .print-footer .mid { text-align: center; }
    .print-footer .end { text-align: right; }

    /* dompdf: Helvetica has the same metrics as the browser's Arial, so the page breaks match */
    body.pdf { font-family: Helvetica, Arial, sans-serif; }

    /* ---- on-screen preview only (never in the PDF) ---- */
    body.preview { background: #e9ecef; padding: 32px 16px; }
    body.preview .profile-card {
        max-width: 8.5in; margin: 0 auto 24px; padding: 0.6in 0.5in;
        background: #fff; border: 1px solid #d1d5db;
    }
    body.preview .watermark { position: absolute; top: 50%; left: 0; }
    .print-only { display: none; }

    @media print {
        body.preview { background: #fff; padding: 0; }
        body.preview .profile-card { max-width: none; margin: 0; padding: 1mm 4mm 0; border: 0; }
        body.preview .watermark { position: fixed; top: 46%; }
        .no-print { display: none !important; }
        .print-only { display: inline; }
        .watermark { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
</style>
