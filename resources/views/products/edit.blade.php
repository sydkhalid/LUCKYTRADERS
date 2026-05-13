@extends('layouts.erp')

@section('title', 'Edit Product')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('products.partials.form', [
            'action' => route('products.update', $product),
            'method' => 'PUT',
            'product' => $product,
        ])
    </div>
@endsection
