@extends('layouts.app')

@section('title', 'Terms Settings')

@section('content')
    @include('settings.partials.header', [
        'active' => 'terms',
        'kicker' => 'Document Footer',
        'title' => 'Terms and Conditions',
        'description' => 'Maintain the legal and billing terms printed at the bottom of invoices, quotations, and receipts.',
        'icon' => 'terms',
    ])

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('settings.terms.update') }}" class="settings-form" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card settings-card">
                    <div class="card-header d-flex align-items-center gap-3">
                        <span class="settings-section-icon bg-label-warning">
                            <svg class="erp-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16l4-2 4 2 4-2 4 2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>
                        </span>
                        <div>
                            <h5 class="mb-0">Invoice Footer Terms</h5>
                            <p class="mb-0 text-muted small">Use one term per line for clean print formatting.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <label class="form-label">Terms and Conditions</label>
                        <textarea name="terms_and_conditions" rows="12" class="form-control settings-textarea-large">{{ old('terms_and_conditions', $settings->terms_and_conditions) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="settings-preview card">
                    <div class="card-body">
                        <p class="settings-kicker mb-2">Print Preview</p>
                        <h5 class="mb-3">Terms Preview</h5>
                        <div class="settings-terms-preview">
                            @forelse (array_filter(preg_split('/\r\n|\r|\n/', (string) old('terms_and_conditions', $settings->terms_and_conditions))) as $line)
                                <p>{{ $line }}</p>
                            @empty
                                <p>No terms added.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-sticky-actions mt-4">
            <div>
                <strong>Terms and Conditions</strong>
                <span>Saved text is reused on billing documents without changing invoice logic.</span>
            </div>
            <button class="btn btn-primary" data-loading-text="Saving terms...">Save Terms</button>
        </div>
    </form>
@endsection
