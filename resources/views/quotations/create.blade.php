@extends('layouts.erp')

@section('title', 'Create Quotation')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('quotations.partials.form', [
            'action' => route('quotations.store'),
            'method' => 'POST',
            'quotation' => null,
        ])
    </div>
@endsection
