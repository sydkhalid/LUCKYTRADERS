@php
    $mode = $mode ?? 'login';
    $canRegister = (bool) ($canRegister ?? false);
    $resetRequest = $request ?? null;
    $availablePanels = ['login', 'forgot'];

    if ($canRegister || $mode === 'register') {
        $availablePanels[] = 'register';
    }

    if ($mode === 'reset') {
        $availablePanels[] = 'reset';
    }

    if ($mode === 'confirm') {
        $availablePanels[] = 'confirm';
    }

    if ($mode === 'verify') {
        $availablePanels[] = 'verify';
    }

    if (! in_array($mode, $availablePanels, true)) {
        $mode = 'login';
    }

    $statusMessage = session('status');
    if ($statusMessage === 'verification-link-sent') {
        $statusMessage = 'A fresh verification link has been sent.';
    }

    $inputClass = 'auth-input';
    $labelClass = 'auth-label';
    $buttonClass = 'auth-primary-button';
@endphp

<main
    class="auth-access min-h-screen overflow-hidden bg-[#eef3f4]"
    data-auth-access
    data-initial-panel="{{ $mode }}"
>
    <style>
        .auth-access {
            background:
                radial-gradient(circle at 18% 18%, rgba(34, 197, 94, 0.14), transparent 26rem),
                linear-gradient(135deg, #f8fbfb 0%, #eef3f4 42%, #dfe8ea 100%);
        }
        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.02fr) minmax(26rem, 0.72fr);
        }
        .auth-visual {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(2rem, 5vw, 4.5rem);
            color: #f8fafc;
            background:
                linear-gradient(150deg, rgba(15, 23, 42, 0.94), rgba(17, 74, 83, 0.9)),
                repeating-linear-gradient(135deg, rgba(255,255,255,0.09) 0 1px, transparent 1px 18px);
        }
        .auth-visual::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 45%;
            background: linear-gradient(0deg, rgba(6, 19, 28, 0.72), transparent);
            pointer-events: none;
        }
        .auth-card {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 4vw, 3rem);
        }
        .auth-form-card {
            width: min(100%, 31rem);
            border: 1px solid rgba(148, 163, 184, 0.28);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.15);
            backdrop-filter: blur(16px);
        }
        .auth-brand-mark {
            display: inline-flex;
            width: 3.25rem;
            height: 3.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: #0f766e;
            color: #fff;
            font-weight: 900;
            letter-spacing: 0.06em;
            box-shadow: 0 14px 34px rgba(15, 118, 110, 0.3);
        }
        .auth-tab {
            border-radius: 999px;
            padding: 0.7rem 1rem;
            color: #475569;
            font-size: 0.84rem;
            font-weight: 900;
            transition: background-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        }
        .auth-tab.is-active {
            background: #0f172a;
            color: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
        }
        .auth-panel[hidden] {
            display: none;
        }
        .auth-label {
            display: block;
            margin-bottom: 0.42rem;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .auth-input {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.95rem;
            background: rgba(248, 250, 252, 0.9);
            padding: 0.88rem 1rem;
            color: #0f172a;
            font-size: 0.95rem;
            font-weight: 700;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }
        .auth-input:focus {
            border-color: #0f766e;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.12);
        }
        .auth-input[aria-invalid="true"] {
            border-color: #e11d48;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.1);
        }
        .auth-primary-button {
            display: inline-flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border-radius: 0.95rem;
            background: #0f172a;
            padding: 0.9rem 1rem;
            color: #fff;
            font-size: 0.95rem;
            font-weight: 900;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.2);
            transition: transform 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
        }
        .auth-primary-button:hover {
            background: #115e59;
            box-shadow: 0 20px 38px rgba(17, 94, 89, 0.22);
        }
        .auth-primary-button:disabled {
            cursor: not-allowed;
            opacity: 0.72;
            transform: none;
        }
        .auth-spinner {
            width: 1rem;
            height: 1rem;
            border-radius: 999px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            animation: auth-spin 0.7s linear infinite;
        }
        @keyframes auth-spin { to { transform: rotate(360deg); } }
        @media (max-width: 960px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-visual { min-height: 19rem; }
        }
        @media (max-width: 640px) {
            .auth-visual { padding: 1.35rem; }
            .auth-card { padding: 1rem; align-items: flex-start; }
            .auth-form-card { border-radius: 1rem; }
            .auth-tab { flex: 1 1 auto; padding-inline: 0.7rem; }
        }
    </style>

    <div class="auth-shell">
        <section class="auth-visual">
            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <span class="auth-brand-mark">LT</span>
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.24em] text-teal-100">LUCKY TRADERS</p>
                        <p class="mt-1 text-sm font-bold text-slate-300">Steel Trading ERP</p>
                    </div>
                </div>

                <div class="mt-16 max-w-2xl">
                    <p class="text-sm font-black uppercase tracking-[0.28em] text-teal-200">Secure ERP Access</p>
                    <h1 class="mt-4 text-4xl font-black tracking-tight text-white sm:text-5xl">Business control center for steel trading.</h1>
                    <p class="mt-5 max-w-xl text-base font-semibold leading-7 text-slate-300">
                        Manage billing, inventory, GST reports, payments, and partner accounts from one protected workspace.
                    </p>
                </div>
            </div>

            <div class="relative z-10 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-black uppercase tracking-wide text-teal-100">Inventory</p>
                    <p class="mt-2 text-2xl font-black">Live Stock</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-black uppercase tracking-wide text-teal-100">Finance</p>
                    <p class="mt-2 text-2xl font-black">Cash Flow</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-black uppercase tracking-wide text-teal-100">Compliance</p>
                    <p class="mt-2 text-2xl font-black">GST Ready</p>
                </div>
            </div>
        </section>

        <section class="auth-card">
            <div class="auth-form-card p-5 sm:p-7">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-teal-700">Account Access</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950" data-auth-heading>Welcome back</h2>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600">ERP</span>
                </div>

                @if ($statusMessage)
                    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800" data-auth-status>
                        {{ $statusMessage }}
                    </div>
                @else
                    <div class="mb-4 hidden rounded-2xl border px-4 py-3 text-sm font-bold" data-auth-status></div>
                @endif

                <div class="mb-6 flex flex-wrap gap-2 rounded-full bg-slate-100 p-1">
                    <button type="button" class="auth-tab" data-auth-switch="login">Login</button>
                    <button type="button" class="auth-tab" data-auth-switch="forgot">Forgot</button>
                    @if ($canRegister || $mode === 'register')
                        <button type="button" class="auth-tab" data-auth-switch="register">First Admin</button>
                    @endif
                    @if ($mode === 'reset')
                        <button type="button" class="auth-tab" data-auth-switch="reset">Reset</button>
                    @endif
                    @if ($mode === 'confirm')
                        <button type="button" class="auth-tab" data-auth-switch="confirm">Confirm</button>
                    @endif
                    @if ($mode === 'verify')
                        <button type="button" class="auth-tab" data-auth-switch="verify">Verify</button>
                    @endif
                </div>

                <section class="auth-panel" data-auth-panel="login" data-title="Welcome back" @if($mode !== 'login') hidden @endif>
                    <form method="POST" action="{{ route('login') }}" data-auth-ajax data-auth-redirect="true">
                        @csrf
                        <div class="space-y-4">
                            <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                            <div>
                                <label for="auth_login_email" class="{{ $labelClass }}">Email</label>
                                <input id="auth_login_email" class="{{ $inputClass }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
                                <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="email"></p>
                            </div>
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <label for="auth_login_password" class="{{ $labelClass }} mb-0">Password</label>
                                    <button type="button" class="text-xs font-black text-teal-700 hover:text-slate-950" data-auth-switch="forgot">Forgot password?</button>
                                </div>
                                <input id="auth_login_password" class="{{ $inputClass }}" type="password" name="password" required autocomplete="current-password">
                                <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password"></p>
                            </div>
                            <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                <input type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-700" name="remember">
                                Keep me signed in
                            </label>
                            <button type="submit" class="{{ $buttonClass }}" data-loading-text="Signing in...">Login to ERP</button>
                        </div>
                    </form>
                </section>

                <section class="auth-panel" data-auth-panel="forgot" data-title="Recover access" @if($mode !== 'forgot') hidden @endif>
                    <form method="POST" action="{{ route('password.email') }}" data-auth-ajax data-auth-redirect="false">
                        @csrf
                        <div class="space-y-4">
                            <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                            <p class="text-sm font-semibold leading-6 text-slate-600">Enter your account email and the reset link will be sent without leaving this page.</p>
                            <div>
                                <label for="auth_forgot_email" class="{{ $labelClass }}">Email</label>
                                <input id="auth_forgot_email" class="{{ $inputClass }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                                <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="email"></p>
                            </div>
                            <button type="submit" class="{{ $buttonClass }}" data-loading-text="Sending link...">Send Reset Link</button>
                            <button type="button" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50" data-auth-switch="login">Back to login</button>
                        </div>
                    </form>
                </section>

                @if ($canRegister || $mode === 'register')
                    <section class="auth-panel" data-auth-panel="register" data-title="Create first admin" @if($mode !== 'register') hidden @endif>
                        <form method="POST" action="{{ route('register') }}" data-auth-ajax data-auth-redirect="true">
                            @csrf
                            <div class="space-y-4">
                                <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                                <div>
                                    <label for="auth_register_name" class="{{ $labelClass }}">Name</label>
                                    <input id="auth_register_name" class="{{ $inputClass }}" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="name"></p>
                                </div>
                                <div>
                                    <label for="auth_register_email" class="{{ $labelClass }}">Email</label>
                                    <input id="auth_register_email" class="{{ $inputClass }}" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="email"></p>
                                </div>
                                <div>
                                    <label for="auth_register_password" class="{{ $labelClass }}">Password</label>
                                    <input id="auth_register_password" class="{{ $inputClass }}" type="password" name="password" required autocomplete="new-password">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password"></p>
                                </div>
                                <div>
                                    <label for="auth_register_password_confirmation" class="{{ $labelClass }}">Confirm Password</label>
                                    <input id="auth_register_password_confirmation" class="{{ $inputClass }}" type="password" name="password_confirmation" required autocomplete="new-password">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password_confirmation"></p>
                                </div>
                                <button type="submit" class="{{ $buttonClass }}" data-loading-text="Creating admin...">Create Admin</button>
                                <button type="button" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50" data-auth-switch="login">Back to login</button>
                            </div>
                        </form>
                    </section>
                @endif

                @if ($mode === 'reset')
                    <section class="auth-panel" data-auth-panel="reset" data-title="Set new password" @if($mode !== 'reset') hidden @endif>
                        <form method="POST" action="{{ route('password.store') }}" data-auth-ajax data-auth-redirect="false" data-success-panel="login">
                            @csrf
                            <input type="hidden" name="token" value="{{ $resetRequest?->route('token') }}">
                            <div class="space-y-4">
                                <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                                <div>
                                    <label for="auth_reset_email" class="{{ $labelClass }}">Email</label>
                                    <input id="auth_reset_email" class="{{ $inputClass }}" type="email" name="email" value="{{ old('email', $resetRequest?->email) }}" required autocomplete="username">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="email"></p>
                                </div>
                                <div>
                                    <label for="auth_reset_password" class="{{ $labelClass }}">New Password</label>
                                    <input id="auth_reset_password" class="{{ $inputClass }}" type="password" name="password" required autocomplete="new-password">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password"></p>
                                </div>
                                <div>
                                    <label for="auth_reset_password_confirmation" class="{{ $labelClass }}">Confirm Password</label>
                                    <input id="auth_reset_password_confirmation" class="{{ $inputClass }}" type="password" name="password_confirmation" required autocomplete="new-password">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password_confirmation"></p>
                                </div>
                                <button type="submit" class="{{ $buttonClass }}" data-loading-text="Resetting password...">Reset Password</button>
                            </div>
                        </form>
                    </section>
                @endif

                @if ($mode === 'confirm')
                    <section class="auth-panel" data-auth-panel="confirm" data-title="Confirm password" @if($mode !== 'confirm') hidden @endif>
                        <form method="POST" action="{{ route('password.confirm') }}" data-auth-ajax data-auth-redirect="true">
                            @csrf
                            <div class="space-y-4">
                                <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                                <p class="text-sm font-semibold leading-6 text-slate-600">Confirm your password before opening this secure area.</p>
                                <div>
                                    <label for="auth_confirm_password" class="{{ $labelClass }}">Password</label>
                                    <input id="auth_confirm_password" class="{{ $inputClass }}" type="password" name="password" required autocomplete="current-password">
                                    <p class="mt-1 hidden text-xs font-bold text-rose-600" data-auth-error-for="password"></p>
                                </div>
                                <button type="submit" class="{{ $buttonClass }}" data-loading-text="Confirming...">Confirm Password</button>
                            </div>
                        </form>
                    </section>
                @endif

                @if ($mode === 'verify')
                    <section class="auth-panel" data-auth-panel="verify" data-title="Verify email" @if($mode !== 'verify') hidden @endif>
                        <div class="space-y-4">
                            <p class="text-sm font-semibold leading-6 text-slate-600">Your account is ready. Verify your email address to finish access setup.</p>
                            <form method="POST" action="{{ route('verification.send') }}" data-auth-ajax data-auth-redirect="false">
                                @csrf
                                <div class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700" data-auth-errors></div>
                                <button type="submit" class="{{ $buttonClass }}" data-loading-text="Sending email...">Resend Verification Email</button>
                            </form>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50">Logout</button>
                            </form>
                        </div>
                    </section>
                @endif
            </div>
        </section>
    </div>
</main>
