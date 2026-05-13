<x-guest-layout title="Forgot Password">
    @include('auth.partials.access-panel', [
        'mode' => 'forgot',
        'canRegister' => $canRegister ?? false,
    ])
</x-guest-layout>
