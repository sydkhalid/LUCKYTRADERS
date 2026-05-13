@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('products.partials.form', [
            'action' => route('products.store'),
            'method' => 'POST',
            'product' => null,
        ])
    </div>
@endsection
