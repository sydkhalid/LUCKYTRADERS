@extends('layouts.erp')

@section('title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Today Sales</p>
            <h2 class="text-2xl font-bold">₹0</h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Cash Balance</p>
            <h2 class="text-2xl font-bold">₹0</h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Stock Value</p>
            <h2 class="text-2xl font-bold">₹0</h2>
        </div>

        <div class="bg-white p-5 rounded shadow">
            <p class="text-gray-500">Pending Collection</p>
            <h2 class="text-2xl font-bold">₹0</h2>
        </div>

    </div>
@endsection
