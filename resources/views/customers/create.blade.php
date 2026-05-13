@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('customers.partials.form', [
            'action' => route('customers.store'),
            'method' => 'POST',
            'customer' => null,
        ])
    </div>
@endsection
