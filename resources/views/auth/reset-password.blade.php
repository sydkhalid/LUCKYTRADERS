<x-guest-layout title="Reset Password">
    @include('auth.partials.access-panel', [
        'mode' => 'reset',
        'canRegister' => false,
        'request' => $request,
    ])
</x-guest-layout>
