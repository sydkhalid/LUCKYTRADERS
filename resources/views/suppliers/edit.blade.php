@extends('layouts.erp')

@section('title', 'Edit Supplier')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('suppliers.partials.form', [
            'action' => route('suppliers.update', $supplier),
            'method' => 'PUT',
            'supplier' => $supplier,
        ])
    </div>
@endsection
