@extends('layouts.app')

@section('title', 'Invoice Settings')

@section('content')
    @php
        $signatureUrl = $settings->signature_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->signature_image) : null;
        $series = [
            ['prefix' => 'gst_invoice_prefix', 'next' => 'next_gst_invoice_no', 'title' => 'GST Invoice', 'hint' => 'Tax invoice series'],
            ['prefix' => 'normal_bill_prefix', 'next' => 'next_normal_bill_no', 'title' => 'Normal Bill', 'hint' => 'Non-GST billing'],
            ['prefix' => 'quotation_prefix', 'next' => 'next_quotation_no', 'title' => 'Quotation', 'hint' => 'Sales offer series'],
            ['prefix' => 'purchase_prefix', 'next' => 'next_purchase_no', 'title' => 'Purchase', 'hint' => 'Supplier purchase series'],
            ['prefix' => 'receipt_prefix', 'next' => 'next_receipt_no', 'title' => 'Receipt', 'hint' => 'Payment receipt series'],
        ];
    @endphp

    @include('settings.partials.header', [
        'active' => 'invoice',
        'kicker' => 'Billing Control',
        'title' => 'Invoice Numbering',
        'description' => 'Manage prefixes, next document numbers, bank text, invoice terms, and authorized signature.',
        'icon' => 'invoice',
    ])

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.invoice.update') }}" enctype="multipart/form-data" class="settings-form" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card settings-card mb-4">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-primary">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h10"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Document Series</h5>
                            <p class="mb-0 text-muted small">Each workflow keeps its own prefix and next number.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($series as $item)
                                <div class="col-md-6 col-xxl-4">
                                    <div class="settings-series-card">
                                        <div>
                                            <strong>{{ $item['title'] }}</strong>
                                            <span>{{ $item['hint'] }}</span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-5">
                                                <label class="form-label">Prefix</label>
                                                <input name="{{ $item['prefix'] }}" value="{{ old($item['prefix'], $settings->{$item['prefix']}) }}" class="form-control text-uppercase" required>
                                            </div>
                                            <div class="col-7">
                                                <label class="form-label">Next No</label>
                                                <input type="number" min="1" name="{{ $item['next'] }}" value="{{ old($item['next'], $settings->{$item['next']}) }}" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card settings-card">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-info">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Invoice Print Text</h5>
                            <p class="mb-0 text-muted small">Reusable footer content for invoices, quotations, and receipts.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <label class="form-label">Terms and Conditions</label>
                                <textarea name="terms_and_conditions" rows="9" class="form-control">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>
                            </div>
                            <div class="col-lg-6">
                                <label class="form-label">Bank Details</label>
                                <textarea name="bank_details" rows="9" class="form-control">{{ old('bank_details', $settings->bank_details) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="settings-preview card mb-4">
                    <div class="card-body">
                        <p class="settings-kicker mb-2">Invoice Preview</p>
                        <div class="settings-invoice-preview">
                            <div class="settings-invoice-preview-head">
                                <span>{{ old('gst_invoice_prefix', $settings->gst_invoice_prefix) }}-{{ str_pad((string) old('next_gst_invoice_no', $settings->next_gst_invoice_no), 5, '0', STR_PAD_LEFT) }}</span>
                                <strong>LUCKY TRADERS</strong>
                            </div>
                            <div class="settings-invoice-preview-lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="settings-invoice-preview-total">
                                <span>Total</span>
                                <strong>{{ $erpCurrency['symbol'] ?? '₹' }} 0.00</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0">Authorized Signature</h5>
                    </div>
                    <div class="card-body">
                        <div class="settings-signature-preview mb-3">
                            @if ($signatureUrl)
                                <img src="{{ $signatureUrl }}" alt="Signature">
                            @else
                                <span>No signature uploaded</span>
                            @endif
                        </div>
                        <label class="settings-upload-zone">
                            <input type="file" name="signature_image" accept="image/*">
                            <span class="settings-upload-icon">
                                <svg class="erp-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                            </span>
                            <strong>Upload signature</strong>
                            <small>Used on printable documents</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-sticky-actions mt-4">
            <div>
                <strong>Invoice Settings</strong>
                <span>Prefixes and next numbers are used by billing services immediately after save.</span>
            </div>
            <button class="btn btn-primary" data-loading-text="Saving invoice settings...">Save Invoice Settings</button>
        </div>
    </form>
@endsection
