@php
    $classes = [
        'draft' => 'bg-gray-100 text-gray-700',
        'sent' => 'bg-blue-100 text-blue-700',
        'accepted' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
        'converted' => 'bg-slate-200 text-slate-800',
    ][$status] ?? 'bg-gray-100 text-gray-700';
@endphp

<span class="rounded px-2 py-1 text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>
