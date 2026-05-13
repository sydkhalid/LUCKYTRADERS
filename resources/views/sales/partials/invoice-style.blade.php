@page {
    size: A4;
    margin: 12mm;
}

body {
    background: #f3f4f6;
    color: #000;
    font-family: DejaVu Sans, Arial, sans-serif;
}

.invoice-page {
    width: 100%;
    margin: 0 auto;
    padding: 14mm;
    border: 1px solid #d1d5db;
    background: #fff;
}

.invoice-header {
    width: 100%;
    border-bottom: 2px solid #c5a32e;
    padding-bottom: 10px;
}

.invoice-brand,
.invoice-meta {
    width: 50%;
    vertical-align: middle;
}

.invoice-brand {
    text-align: center;
}

.invoice-logo {
    max-width: 110px;
    max-height: 78px;
    object-fit: contain;
}

.invoice-logo-mark {
    display: inline-block;
    width: 72px;
    height: 72px;
    border: 2px solid #c5a32e;
    border-radius: 50%;
    color: #111827;
    font-size: 24px;
    font-weight: 900;
    line-height: 70px;
    text-align: center;
}

.invoice-company-name {
    margin-top: 5px;
    color: #000;
    font-size: 22px;
    font-weight: 900;
    letter-spacing: 0;
}

.invoice-meta {
    text-align: right;
}

.invoice-meta h2 {
    margin: 0 0 8px;
    color: #111827;
    font-size: 19px;
    font-weight: 900;
    text-transform: uppercase;
}

.invoice-meta p {
    margin: 2px 0;
    font-size: 14px;
    line-height: 1.6;
}

.invoice-info {
    width: 100%;
    margin-top: 38px;
}

.invoice-info td {
    width: 50%;
    vertical-align: top;
}

.invoice-info .left {
    padding-right: 22px;
}

.invoice-info .right {
    padding-left: 22px;
    text-align: left;
}

.invoice-section-title {
    margin: 0 0 6px;
    color: #111827;
    font-size: 16px;
    font-weight: 900;
}

.invoice-party {
    font-size: 14px;
    line-height: 1.55;
}

.invoice-party strong {
    font-size: 15px;
}

.invoice-items {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    font-size: 13px;
}

.invoice-items th,
.invoice-items td {
    border: 1px solid #cfd4dc;
    padding: 7px 8px;
    vertical-align: top;
}

.invoice-items th {
    background: #f3f4f6;
    color: #111827;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.amount-cell {
    text-align: right;
    white-space: nowrap;
    font-weight: 900;
}

.invoice-total-summary {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
    font-size: 14px;
}

.invoice-total-summary td {
    border: 1px solid #cfd4dc;
    padding: 6px 8px;
}

.invoice-total-summary td:first-child {
    width: 82%;
    text-align: right;
    font-weight: 800;
}

.invoice-total-summary td:last-child {
    width: 18%;
    text-align: right;
    white-space: nowrap;
}

.invoice-total-highlight td {
    background: #c5a32e;
    color: #000;
    font-weight: 900;
}

.amount-words {
    margin-top: 12px;
    border: 1px dashed #cfd4dc;
    background: #fafafa;
    padding: 10px;
    text-align: center;
    font-size: 14px;
    font-weight: 900;
}

.invoice-note {
    margin-top: 10px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    padding: 8px 10px;
    font-size: 13px;
}

.invoice-summary-section {
    width: 100%;
    margin-top: 22px;
    font-size: 14px;
}

.invoice-summary-section td {
    width: 50%;
    vertical-align: top;
}

.invoice-bank {
    padding-right: 18px;
    line-height: 1.55;
}

.invoice-signature {
    padding-left: 18px;
    text-align: right;
}

.invoice-signature img {
    max-width: 160px;
    max-height: 62px;
    margin-top: 10px;
    object-fit: contain;
}

.invoice-signature-space {
    height: 56px;
}

.invoice-signature-line {
    margin-top: 6px;
    font-weight: 900;
}

.invoice-footer-note {
    margin-top: 12px;
    color: #4b5563;
    font-size: 11px;
    line-height: 1.45;
}

.print-actions {
    width: 100%;
    max-width: 210mm;
    margin: 16px auto;
    text-align: right;
}

.print-actions button,
.print-actions a {
    display: inline-block;
    border: 0;
    border-radius: 4px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
}

.print-actions button {
    background: #c5a32e;
    color: #000;
}

.print-actions a {
    margin-left: 6px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #111827;
}

@media print {
    body {
        background: #fff !important;
        font-size: 15px !important;
        line-height: 1.45 !important;
    }

    .invoice-page {
        padding: 8mm !important;
        border: 0 !important;
    }

    .print-actions {
        display: none !important;
    }

    .invoice-items th,
    .invoice-items td {
        padding: 7px !important;
    }
}
