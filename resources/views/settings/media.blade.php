@extends('layouts.erp')

@section('title', 'Logo and Signature Settings')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Logo and Signature Upload</h2>
            <p class="text-sm text-gray-500">Images are stored using Laravel public storage and rendered on PDFs.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('settings.company') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Company Settings</a>
            <a href="{{ route('settings.invoice') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Invoice Settings</a>
            <a href="{{ route('settings.bank') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Bank Details</a>
            <a href="{{ route('settings.terms') }}" class="rounded border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Terms</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('settings.media.update') }}" enctype="multipart/form-data" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm" data-ajax-form>
        @csrf
        @method('PATCH')

        <div class="grid gap-8 md:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Company Logo</label>
                <div class="mt-3 rounded border border-gray-200 bg-gray-50 p-4">
                    @if ($companySettings->logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($companySettings->logo) }}" alt="Company logo" class="mb-4 h-20 max-w-64 rounded border border-gray-200 bg-white object-contain p-2">
                    @else
                        <p class="mb-4 text-sm text-gray-500">No logo uploaded.</p>
                    @endif
                    <input type="file" name="logo" accept="image/*" class="block text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Authorized Signature</label>
                <div class="mt-3 rounded border border-gray-200 bg-gray-50 p-4">
                    @if ($invoiceSettings->signature_image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($invoiceSettings->signature_image) }}" alt="Signature" class="mb-4 h-20 max-w-64 rounded border border-gray-200 bg-white object-contain p-2">
                    @else
                        <p class="mb-4 text-sm text-gray-500">No signature uploaded.</p>
                    @endif
                    <input type="file" name="signature_image" accept="image/*" class="block text-sm text-gray-700 file:mr-4 file:rounded file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button class="rounded bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Upload Images</button>
        </div>
    </form>
@endsection
