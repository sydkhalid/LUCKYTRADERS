@extends('layouts.app')

@section('title', 'Logo and Signature Settings')

@section('content')
    @php
        $logoUrl = $companySettings->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($companySettings->logo) : null;
        $signatureUrl = $invoiceSettings->signature_image ? \Illuminate\Support\Facades\Storage::disk('public')->url($invoiceSettings->signature_image) : null;
    @endphp

    @include('settings.partials.header', [
        'active' => 'media',
        'kicker' => 'Brand Assets',
        'title' => 'Logo and Signature',
        'description' => 'Upload optimized company images for the ERP shell, invoices, quotations, receipts, and PDFs.',
        'icon' => 'media',
    ])

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.media.update') }}" enctype="multipart/form-data" class="settings-form" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-lg-6">
                <div class="card settings-card h-100">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-primary">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Company Logo</h5>
                            <p class="mb-0 text-muted small">Shown in the header and printable documents.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-media-preview mb-4">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Company logo">
                            @else
                                <span>LT</span>
                            @endif
                        </div>
                        <label class="settings-upload-zone">
                            <input type="file" name="logo" accept="image/*">
                            <span class="settings-upload-icon">
                                <svg class="erp-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                            </span>
                            <strong>Upload logo</strong>
                            <small>JPG, PNG, or WEBP up to 2 MB</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="card settings-card h-100">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-success">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Authorized Signature</h5>
                            <p class="mb-0 text-muted small">Printed on invoice and voucher documents.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="settings-media-preview mb-4">
                            @if ($signatureUrl)
                                <img src="{{ $signatureUrl }}" alt="Signature">
                            @else
                                <span>Sign</span>
                            @endif
                        </div>
                        <label class="settings-upload-zone">
                            <input type="file" name="signature_image" accept="image/*">
                            <span class="settings-upload-icon">
                                <svg class="erp-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                            </span>
                            <strong>Upload signature</strong>
                            <small>JPG, PNG, or WEBP up to 2 MB</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-sticky-actions mt-4">
            <div>
                <strong>Media Settings</strong>
                <span>The controller keeps the same optimized upload and old-file cleanup flow.</span>
            </div>
            <button class="btn btn-primary" data-loading-text="Uploading images...">Upload Images</button>
        </div>
    </form>
@endsection
