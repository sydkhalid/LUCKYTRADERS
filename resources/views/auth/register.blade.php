<x-guest-layout title="Create First Admin">
    @include('auth.partials.access-panel', [
        'mode' => 'register',
        'canRegister' => true,
    ])
</x-guest-layout>
