@extends('layouts.erp')

@section('title', 'Users & Roles')

@section('content')
    <x-erp.page-header
        title="Users & Roles"
        description="Create staff users and assign ERP access by role."
        kicker="Access Control"
    >
        <x-slot:actions>
            <a href="{{ route('users.create') }}" class="erp-primary-button">Create User</a>
        </x-slot:actions>
    </x-erp.page-header>

    <form id="userFilters" class="mb-5 grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <div>
            <label class="mb-1 block text-xs font-black uppercase tracking-wide text-slate-500">Role</label>
            <select name="status" data-searchable class="w-full">
                <option value="">All Users</option>
                <option value="Super Admin">Super Admin</option>
                <option value="Admin">Admin</option>
                <option value="Staff">Staff</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="erp-primary-button w-full">Apply</button>
        </div>
        <div class="flex items-end">
            <button type="button" data-reset-filters class="erp-secondary-button w-full">Reset</button>
        </div>
    </form>

    <x-erp.datatable
        id="usersTable"
        :ajax-url="route('erp.datatables', 'users')"
        filter-form="#userFilters"
        search-placeholder="Search user, email, role..."
        empty="No staff users found."
    >
        <thead>
            <tr>
                <th class="px-4 py-3" data-column="name">Name</th>
                <th class="px-4 py-3" data-column="email">Email</th>
                <th class="px-4 py-3" data-column="role">Role</th>
                <th class="px-4 py-3" data-column="is_admin">Admin</th>
                <th class="px-4 py-3" data-column="created_at">Created</th>
                <th class="px-4 py-3 text-right" data-column="actions" data-orderable="false" data-searchable="false">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-erp.datatable>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-base font-black text-slate-950">Role Permission Matrix</h3>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($roles as $role)
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h4 class="font-black text-slate-950">{{ $role->name }}</h4>
                        <span class="erp-badge erp-badge-neutral">{{ $role->permissions->count() }} permissions</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($role->permissions->sortBy('name') as $permission)
                            <span class="rounded-lg bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">{{ str_replace('_', ' ', $permission->name) }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
