@extends('layouts.erp')

@section('title', 'Edit Quotation')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('quotations.partials.form', [
            'action' => route('quotations.update', $quotation),
            'method' => 'PUT',
            'quotation' => $quotation,
        ])
    </div>
@endsection
