<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')
            ->latest('id')
            ->paginate(15);

        $roles = Role::with('permissions')
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_admin' => in_array($data['role'], ['Super Admin', 'Admin'], true),
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff user created successfully.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ]);

        $this->ensureLastSuperAdminIsKept($user, $data['role']);

        $updates = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_admin' => in_array($data['role'], ['Super Admin', 'Admin'], true),
        ];

        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->forceFill($updates)->save();
        $user->syncRoles([$data['role']]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Staff user updated successfully.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    private function roles()
    {
        return Role::orderBy('name')->get();
    }

    private function ensureLastSuperAdminIsKept(User $user, string $newRole): void
    {
        if ($newRole === 'Super Admin' || ! $user->hasRole('Super Admin')) {
            return;
        }

        if (User::role('Super Admin')->whereKeyNot($user->id)->doesntExist()) {
            throw ValidationException::withMessages([
                'role' => 'At least one Super Admin user is required.',
            ]);
        }
    }
}
