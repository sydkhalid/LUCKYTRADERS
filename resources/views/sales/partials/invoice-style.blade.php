@page {
    size: A4;
    margin: 10mm;
}

body {
    background: #f2f2f2;
    color: #050505;
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
}

.invoice-page {
    width: 100%;
    margin: 0 auto;
    padding: 10mm 9mm 13mm;
    background: #fff;
    color: #050505;
}

.invoice-header {
    width: 100%;
    border-bottom: 2px solid #b8a342;
    padding-bottom: 9px;
}

.invoice-brand,
.invoice-meta {
    width: 50%;
    vertical-align: bottom;
}

.invoice-logo {
    display: block;
    width: 76px;
    max-width: 76px;
    max-height: 76px;
    margin-bottom: 5px;
    object-fit: contain;
}

.invoice-logo-mark {
    display: block;
    width: 74px;
    height: 74px;
    margin-bottom: 5px;
    border: 2px solid #c5a32e;
    background: #111827;
    color: #fff;
    font-size: 23px;
    font-weight: 900;
    line-height: 72px;
    text-align: center;
}

.invoice-company-name {
    color: #050505;
    font-size: 14px;
    font-weight: 900;
    letter-spacing: 0;
    text-transform: uppercase;
}

.invoice-meta {
    text-align: right;
}

.invoice-meta p {
    margin: 2px 0;
    color: #050505;
    font-size: 20px;
    line-height: 1.22;
}

.invoice-meta strong {
    font-weight: 900;
}

.invoice-info {
    width: 100%;
    margin-top: 26px;
}

.invoice-info td {
    width: 50%;
    vertical-align: top;
}

.invoice-info .left {
    padding-right: 24px;
}

.invoice-info .right {
    padding-left: 24px;
}

.invoice-section-title {
    margin: 0 0 7px;
    color: #111827;
    font-size: 14px;
    font-weight: 500;
}

.invoice-party {
    color: #050505;
    font-size: 10px;
    line-height: 1.45;
}

.invoice-party strong {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
}

.invoice-items,
.invoice-total-summary,
.invoice-eway {
    width: 100%;
    border-collapse: collapse;
}

.invoice-items {
    margin-top: 14px;
    font-size: 10px;
}

.invoice-items th,
.invoice-items td {
    border: 1px solid #d6d6d6;
    padding: 7px 8px;
    vertical-align: middle;
}

.invoice-items th {
    background: #fff;
    color: #050505;
    font-size: 9px;
    font-weight: 900;
    text-transform: uppercase;
}

.invoice-items td {
    font-size: 10px;
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
    margin-top: 0;
    font-size: 11px;
}

.invoice-total-summary td {
    border: 1px solid #d6d6d6;
    padding: 8px 9px;
}

.invoice-total-summary td:first-child {
    width: 80%;
    text-align: right;
    font-weight: 900;
}

.invoice-total-summary td:last-child {
    width: 20%;
    text-align: right;
    white-space: nowrap;
    font-weight: 500;
}

.invoice-total-highlight td {
    color: #050505;
    font-weight: 900;
}

.amount-words {
    margin-top: 8px;
    border: 1px dashed #dedede;
    background: #fff;
    padding: 9px 10px;
    text-align: center;
    font-size: 10px;
    font-weight: 900;
}

.invoice-eway {
    margin-top: 0;
    border: 1px solid #d6d6d6;
    border-top: 0;
    font-size: 10px;
}

.invoice-eway td {
    width: 25%;
    padding: 8px 10px;
    vertical-align: top;
}

.invoice-eway strong {
    font-weight: 900;
}

.invoice-note {
    margin-top: 10px;
    border: 1px solid #e5e7eb;
    background: #fff;
    padding: 8px 10px;
    font-size: 10px;
}

.invoice-summary-section {
    width: 100%;
    margin-top: 24px;
    font-size: 10px;
}

.invoice-summary-section td {
    width: 50%;
    vertical-align: top;
}

.invoice-bank {
    padding-left: 6px;
    padding-right: 18px;
    line-height: 1.55;
}

.invoice-bank-title,
.invoice-signature-title {
    display: block;
    margin-bottom: 18px;
    font-weight: 900;
}

.invoice-signature {
    padding-left: 18px;
    text-align: right;
}

.invoice-signature img {
    max-width: 145px;
    max-height: 86px;
    margin-top: 4px;
    object-fit: contain;
}

.invoice-signature-space {
    height: 78px;
}

.invoice-signature-line {
    margin-top: 4px;
    font-weight: 900;
}

.invoice-footer-note {
    display: none;
}

.print-actions {
    width: 100%;
    max-width: 210mm;
    margin: 14px auto;
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
    background: #111827;
    color: #fff;
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
    }

    .invoice-page {
        padding: 0 !important;
    }

    .print-actions {
        display: none !important;
    }
}
