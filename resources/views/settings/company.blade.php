@extends('layouts.app')

@section('title', 'Company Settings')

@section('content')
    @php
        $logoUrl = $settings->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings->logo) : null;
        $systemSummary = [
            ['label' => 'Currency', 'value' => old('currency', $systemSettings->currency ?? 'INR')],
            ['label' => 'Default Tax', 'value' => rtrim(rtrim((string) old('default_tax', $systemSettings->default_tax ?? 18), '0'), '.').'%'],
            ['label' => 'Low Stock Alert', 'value' => old('low_stock_threshold', $systemSettings->low_stock_threshold ?? 10)],
        ];
    @endphp

    @include('settings.partials.header', [
        'active' => 'company',
        'kicker' => 'Company Control',
        'title' => 'Company Profile',
        'description' => 'Branding, GST, address, stock alerts, tax defaults, and theme preferences used across every ERP screen.',
        'icon' => 'company',
    ])

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="settings-form" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card settings-card mb-4">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-primary">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Business Identity</h5>
                            <p class="mb-0 text-muted small">Invoice and report source details.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input name="company_name" value="{{ old('company_name', $settings->company_name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST Number</label>
                                <input name="gst_number" value="{{ old('gst_number', $settings->gst_number) }}" class="form-control text-uppercase">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input name="phone" value="{{ old('phone', $settings->phone) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $settings->email) }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" rows="3" class="form-control">{{ old('address', $settings->address) }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input name="state" value="{{ old('state', $settings->state) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input name="city" value="{{ old('city', $settings->city) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input name="pincode" value="{{ old('pincode', $settings->pincode) }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card settings-card mb-4">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-info">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5"/><path d="M8 19V9"/><path d="M12 19V7"/><path d="M16 19v-4"/><path d="M20 19V11"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Business Defaults</h5>
                            <p class="mb-0 text-muted small">Operational settings used by products, invoices, and filters.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" min="0" step="0.001" name="low_stock_threshold" value="{{ old('low_stock_threshold', $systemSettings->low_stock_threshold ?? 10) }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Default Tax (%)</label>
                                <input type="number" min="0" max="100" step="0.01" name="default_tax" value="{{ old('default_tax', $systemSettings->default_tax ?? 18) }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    @foreach (['INR', 'USD', 'EUR', 'GBP', 'AED', 'SGD'] as $currency)
                                        <option value="{{ $currency }}" @selected(old('currency', $systemSettings->currency ?? 'INR') === $currency)>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Date Format</label>
                                <select name="date_format" class="form-select">
                                    @foreach (['d M Y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $format)
                                        <option value="{{ $format }}" @selected(old('date_format', $systemSettings->date_format ?? 'd M Y') === $format)>{{ now()->format($format) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card settings-card">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-warning">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="M3 12h18"/><path d="m19 5-3 3"/><path d="m5 19 3-3"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Theme Appearance</h5>
                            <p class="mb-0 text-muted small">Controls the ERP shell style after save.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Theme Mode</label>
                                <select name="theme_mode" class="form-select">
                                    <option value="light" @selected(old('theme_mode', $systemSettings->theme_mode ?? 'light') === 'light')>Light</option>
                                    <option value="dark" @selected(old('theme_mode', $systemSettings->theme_mode ?? 'light') === 'dark')>Dark</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Theme Color</label>
                                <input type="color" name="theme_color" value="{{ old('theme_color', $systemSettings->theme_color ?? '#696cff') }}" class="settings-color-input form-control form-control-color">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Sidebar Style</label>
                                <select name="sidebar_style" class="form-select">
                                    <option value="dark" @selected(old('sidebar_style', $systemSettings->sidebar_style ?? 'dark') === 'dark')>Dark</option>
                                    <option value="light" @selected(old('sidebar_style', $systemSettings->sidebar_style ?? 'dark') === 'light')>Light</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Header Style</label>
                                <select name="header_style" class="form-select">
                                    <option value="light" @selected(old('header_style', $systemSettings->header_style ?? 'light') === 'light')>Light</option>
                                    <option value="dark" @selected(old('header_style', $systemSettings->header_style ?? 'light') === 'dark')>Dark</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="settings-preview card mb-4">
                    <div class="card-body text-center">
                        <div class="settings-logo-preview mx-auto mb-3">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Company logo">
                            @else
                                <span>LT</span>
                            @endif
                        </div>
                        <p class="settings-kicker mb-1">Lucky Traders Brand</p>
                        <h4 class="mb-1">{{ old('company_name', $settings->company_name ?: 'LUCKY TRADERS') }}</h4>
                        <p class="mb-0 text-muted">{{ old('gst_number', $settings->gst_number) ?: 'GST details not added' }}</p>
                    </div>
                </div>

                <div class="card settings-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Logo Upload</h5>
                    </div>
                    <div class="card-body">
                        <label class="settings-upload-zone">
                            <input type="file" name="logo" accept="image/*">
                            <span class="settings-upload-icon">
                                <svg class="erp-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                            </span>
                            <strong>Upload company logo</strong>
                            <small>JPG, PNG, or WEBP up to 2 MB</small>
                        </label>
                    </div>
                </div>

                <div class="row g-3">
                    @foreach ($systemSummary as $item)
                        <div class="col-12 col-sm-4 col-xl-12">
                            <div class="settings-mini-card">
                                <span>{{ $item['label'] }}</span>
                                <strong>{{ $item['value'] }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="settings-sticky-actions mt-4">
            <div>
                <strong>Company Settings</strong>
                <span>Saved values update invoices, reports, and shell theme.</span>
            </div>
            <button class="btn btn-primary" data-loading-text="Saving settings...">Save Company Settings</button>
        </div>
    </form>
@endsection
