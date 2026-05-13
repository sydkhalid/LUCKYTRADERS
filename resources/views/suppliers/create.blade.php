@extends('layouts.erp')

@section('title', 'Add Supplier')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('suppliers.partials.form', [
            'action' => route('suppliers.store'),
            'method' => 'POST',
            'supplier' => null,
        ])
    </div>
@endsection
