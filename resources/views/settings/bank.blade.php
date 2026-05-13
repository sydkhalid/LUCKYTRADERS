@extends('layouts.app')

@section('title', 'Bank Details Settings')

@section('content')
    @include('settings.partials.header', [
        'active' => 'bank',
        'kicker' => 'Payment Details',
        'title' => 'Bank Details',
        'description' => 'These details are printed on invoices, quotations, receipts, and payment reports.',
        'icon' => 'bank',
    ])

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.bank.update') }}" class="settings-form" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card settings-card">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-primary">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10h18"/><path d="M5 10V8l7-5 7 5v2"/><path d="M6 10v8"/><path d="M10 10v8"/><path d="M14 10v8"/><path d="M18 10v8"/><path d="M4 18h16"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Printable Bank Text</h5>
                            <p class="mb-0 text-muted small">Keep account name, account number, branch, IFSC, and payment instructions here.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Bank Details</label>
                        <textarea name="bank_details" rows="12" class="form-control settings-textarea-large">{{ old('bank_details', $settings->bank_details) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="settings-preview card">
                    <div class="card-body">
                        <p class="settings-kicker mb-2">Invoice Side Panel</p>
                        <h5 class="mb-3">Bank Details Preview</h5>
                        <div class="settings-bank-preview">
                            @forelse (array_filter(preg_split('/\r\n|\r|\n/', (string) old('bank_details', $settings->bank_details))) as $line)
                                <p>{{ $line }}</p>
                            @empty
                                <p>No bank details added.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-sticky-actions mt-4">
            <div>
                <strong>Bank Details</strong>
                <span>This content appears on customer-facing billing documents.</span>
            </div>
            <button class="btn btn-primary" data-loading-text="Saving bank details...">Save Bank Details</button>
        </div>
    </form>
@endsection
