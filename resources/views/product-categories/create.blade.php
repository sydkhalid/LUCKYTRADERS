@extends('layouts.erp')

@section('title', 'Add Product Category')

@section('content')
    <div class="max-w-3xl rounded bg-white p-6 shadow">
        @include('product-categories.partials.form', [
            'action' => route('product-categories.store'),
            'method' => 'POST',
            'category' => null,
        ])
    </div>
@endsection
