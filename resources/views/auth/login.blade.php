<x-guest-layout title="Login">
    @include('auth.partials.access-panel', [
        'mode' => 'login',
        'canRegister' => $canRegister ?? false,
    ])
</x-guest-layout>
