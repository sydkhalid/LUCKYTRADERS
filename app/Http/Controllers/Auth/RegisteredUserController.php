<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): RedirectResponse|View
    {
        if (User::query()->exists()) {
            return redirect()->route('login')->with('status', 'Initial admin already exists. Please log in.');
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (User::query()->exists()) {
            return redirect()->route('login')->with('status', 'Initial admin already exists. Please log in.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->assignInitialAdminRole($user);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function assignInitialAdminRole(User $user): void
    {
        $updates = [];

        if (Schema::hasColumn('users', 'is_admin')) {
            $updates['is_admin'] = true;
        }

        if (Schema::hasColumn('users', 'role')) {
            $updates['role'] = 'Super Admin';
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }

        if (Schema::hasTable('roles') && Role::where('name', 'Super Admin')->exists()) {
            $user->assignRole('Super Admin');
        }
    }
}
