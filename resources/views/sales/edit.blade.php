@extends('layouts.erp')

@section('title', 'Edit Sale')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('sales.partials.form', [
            'action' => route('sales.update', $sale),
            'method' => 'PUT',
            'sale' => $sale,
        ])
    </div>
@endsection
