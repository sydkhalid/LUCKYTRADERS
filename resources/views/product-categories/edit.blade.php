@extends('layouts.erp')

@section('title', 'Edit Product Category')

@section('content')
    <div class="max-w-3xl rounded bg-white p-6 shadow">
        @include('product-categories.partials.form', [
            'action' => route('product-categories.update', $productCategory),
            'method' => 'PUT',
            'category' => $productCategory,
        ])
    </div>
@endsection
