@extends('layouts.erp')

@section('title', 'Edit Staff User')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Edit Staff User</h2>
            <p class="text-sm text-gray-500">{{ $user->name }} - {{ $user->email }}</p>
        </div>
        <a href="{{ route('users.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Back</a>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}" class="rounded bg-white p-6 shadow" data-ajax-form>
        @csrf
        @method('PUT')

        @include('users.partials.form', ['user' => $user])

        <div class="mt-6 flex justify-end">
            <button class="rounded bg-slate-800 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-700">Update User</button>
        </div>
    </form>
@endsection
