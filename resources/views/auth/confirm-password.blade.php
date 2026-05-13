<x-guest-layout title="Confirm Password">
    @include('auth.partials.access-panel', [
        'mode' => 'confirm',
        'canRegister' => false,
    ])
</x-guest-layout>
