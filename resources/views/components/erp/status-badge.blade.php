@props([
    'value',
])

@php
    $normalized = strtolower(str_replace([' ', '_'], '-', (string) $value));
    $tone = match ($normalized) {
        'paid', 'active', 'gst', 'accepted', 'converted', 'cash', 'bank', 'upi', 'receipt', 'increase' => 'success',
        'partial', 'pending', 'draft', 'sent', 'cheque', 'credit' => 'warning',
        'inactive', 'cancelled', 'closed', 'rejected', 'non-gst', 'payment', 'decrease' => 'danger',
        default => 'neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => 'erp-badge erp-badge-'.$tone]) }}>
    {{ $value }}
</span>
