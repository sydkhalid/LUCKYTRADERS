<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.4; }
        h1, h2, h3, p { margin: 0; }
        .document { width: 100%; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .logo { max-height: 52px; max-width: 120px; margin-bottom: 6px; object-fit: contain; }
        .company-name { font-size: 24px; font-weight: 700; letter-spacing: .5px; }
        .company-address { margin-top: 3px; color: #4b5563; font-size: 11px; }
        .title-box { text-align: right; }
        .title { font-size: 18px; font-weight: 700; text-transform: uppercase; }
        .muted { color: #6b7280; }
        .section { margin-top: 12px; }
        .section-title { background: #f3f4f6; border: 1px solid #d1d5db; font-size: 10px; font-weight: 700; padding: 6px 8px; text-transform: uppercase; }
        .box { border: 1px solid #d1d5db; padding: 8px; }
        .grid-2 { width: 100%; }
        .grid-2 td { width: 50%; vertical-align: top; }
        .meta td { padding: 3px 0; }
        .label { color: #6b7280; font-weight: 700; }
        table { border-collapse: collapse; width: 100%; }
        .items th { background: #111827; border: 1px solid #111827; color: #fff; font-size: 9px; padding: 6px 5px; text-transform: uppercase; }
        .items td { border: 1px solid #d1d5db; padding: 6px 5px; vertical-align: top; }
        .items tfoot td { font-weight: 700; }
        .right { text-align: right; }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .total-row td { background: #f9fafb; font-size: 12px; }
        .words { border: 1px solid #d1d5db; margin-top: 10px; padding: 8px; }
        .footer-grid { margin-top: 18px; width: 100%; }
        .footer-grid td { width: 50%; vertical-align: top; }
        .terms { color: #374151; font-size: 10px; }
        .terms p { margin-bottom: 2px; }
        .bank-details { margin-top: 8px; color: #374151; font-size: 10px; white-space: pre-line; }
        .signature { padding-top: 45px; text-align: right; }
        .signature-image { display: block; height: 44px; margin: 0 0 4px auto; max-width: 170px; object-fit: contain; }
        .signature-line { border-top: 1px solid #111827; display: inline-block; min-width: 180px; padding-top: 5px; text-align: center; }
        .badge { border: 1px solid #111827; display: inline-block; font-size: 9px; font-weight: 700; margin-top: 6px; padding: 2px 6px; text-transform: uppercase; }
        .page-break { page-break-before: always; }
        .avoid-break { page-break-inside: avoid; }
        @media print {
            @page { size: A4; margin: 24px; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        @yield('styles')
    </style>
</head>
<body>
    <main class="document">
        @yield('content')
    </main>
</body>
</html>
