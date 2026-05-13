@extends('layouts.app')

@section('title', 'New Purchase')

@section('content')
    <div class="rounded bg-white p-6 shadow">
        @include('purchases.partials.form', [
            'action' => route('purchases.store'),
            'method' => 'POST',
            'purchase' => null,
        ])
    </div>
@endsection
