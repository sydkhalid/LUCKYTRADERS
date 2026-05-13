@extends('layouts.erp')

@section('title', 'Edit Customer')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('customers.partials.form', [
            'action' => route('customers.update', $customer),
            'method' => 'PUT',
            'customer' => $customer,
        ])
    </div>
@endsection
