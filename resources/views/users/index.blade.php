@extends('layouts.erp')

@section('title', 'Users & Roles')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Users & Roles</h2>
            <p class="text-sm text-gray-500">Create staff users and assign ERP access by role.</p>
        </div>
        <a href="{{ route('users.create') }}" class="rounded bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Create User</a>
    </div>

    <div class="mb-6 overflow-hidden rounded bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Admin</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $user->primaryRoleName() }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('users.edit', $user) }}" class="font-semibold text-slate-700 hover:text-slate-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No staff users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mb-5">
        {{ $users->links() }}
    </div>

    <div class="rounded bg-white p-5 shadow">
        <h3 class="mb-4 text-base font-semibold text-gray-900">Role Permission Matrix</h3>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roles as $role)
                <div class="rounded border border-gray-200 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="font-semibold text-gray-900">{{ $role->name }}</h4>
                        <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-600">{{ $role->permissions->count() }} permissions</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($role->permissions->sortBy('name') as $permission)
                            <span class="rounded bg-slate-50 px-2 py-1 text-xs font-medium text-slate-700">{{ str_replace('_', ' ', $permission->name) }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
