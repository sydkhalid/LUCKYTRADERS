@extends('layouts.erp')

@section('title', 'Product Categories')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Product Categories</h2>
            <p class="text-sm text-gray-500">Steel product groups used for stock, purchase, and billing.</p>
        </div>
        <a href="{{ route('product-categories.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Add Category</a>
    </div>

    <form method="GET" action="{{ route('product-categories.index') }}" class="mb-5 flex flex-wrap gap-3 rounded bg-white p-4 shadow">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search category, description, or status" class="min-w-0 flex-1 rounded border-gray-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
        <button class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Search</button>
        @if ($search !== '')
            <a href="{{ route('product-categories.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3 text-right">Products</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $category->description ?: '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $category->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($category->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('product-categories.show', $category) }}" class="font-semibold text-slate-700 hover:text-slate-900">View</a>
                                <a href="{{ route('product-categories.edit', $category) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                                @can('delete_records')
                                    <form method="POST" action="{{ route('product-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-semibold text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $categories->withQueryString()->links() }}
    </div>
@endsection
