@extends('layouts.app')

@section('title', 'Create Sale')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('sales.partials.form', [
            'action' => route('sales.store'),
            'method' => 'POST',
            'sale' => null,
        ])
    </div>
@endsection
