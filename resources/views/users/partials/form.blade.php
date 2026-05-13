@php
    $selectedRole = old('role', $user?->primaryRoleName());
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $user?->name) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="role">Role</label>
        <select id="role" name="role" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" required>
            <option value="">Select role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected($selectedRole === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="password">{{ $user ? 'New Password' : 'Password' }}</label>
        <input id="password" type="password" name="password" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" @if (! $user) required @endif>
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="password_confirmation">Confirm Password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="w-full rounded border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" @if (! $user) required @endif>
    </div>
</div>
