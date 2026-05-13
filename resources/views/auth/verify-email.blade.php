<x-guest-layout title="Verify Email">
    @include('auth.partials.access-panel', [
        'mode' => 'verify',
        'canRegister' => false,
    ])
</x-guest-layout>
