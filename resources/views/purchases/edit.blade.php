@extends('layouts.erp')

@section('title', 'Edit Purchase')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('purchases.partials.form', [
            'action' => route('purchases.update', $purchase),
            'method' => 'PUT',
            'purchase' => $purchase,
        ])
    </div>
@endsection
