<div class="overflow-hidden rounded bg-white shadow">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
            <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Product</th>
                <th class="px-4 py-3">Movement</th>
                <th class="px-4 py-3">Reference</th>
                <th class="px-4 py-3 text-right">Qty</th>
                <th class="px-4 py-3 text-right">Rate</th>
                <th class="px-4 py-3 text-right">Value</th>
                <th class="px-4 py-3">Remarks</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($movements as $movement)
                @php
                    $adjustment = $movement->reference_type === 'stock_adjustment'
                        ? ($adjustmentsById[$movement->reference_id] ?? null)
                        : null;
                    $movementLabel = match ($movement->movement_type) {
                        'purchase_in' => 'Purchase In',
                        'sale_out' => 'Sale Out',
                        'sales_return_in' => 'Sales Return In',
                        'purchase_return_out' => 'Purchase Return Out',
                        'adjustment' => match ($movement->reference_type) {
                            'sales_return' => 'Sales Return',
                            'purchase_return' => 'Purchase Return',
                            default => $adjustment ? 'Adjustment '.$adjustment->typeLabel() : 'Adjustment',
                        },
                        default => ucfirst(str_replace('_', ' ', $movement->movement_type)),
                    };
                    $isIn = $movement->movement_type === 'purchase_in'
                        || $movement->movement_type === 'sales_return_in'
                        || $movement->reference_type === 'sales_return'
                        || ($movement->movement_type === 'adjustment' && $adjustment?->adjustment_type === 'increase');
                    $isOut = $movement->movement_type === 'sale_out'
                        || $movement->movement_type === 'purchase_return_out'
                        || $movement->reference_type === 'purchase_return'
                        || ($movement->movement_type === 'adjustment' && $adjustment?->adjustment_type === 'decrease');
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $movement->movement_date?->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $movement->product?->name ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="rounded px-2 py-1 text-xs font-semibold {{ $isIn ? 'bg-emerald-100 text-emerald-700' : ($isOut ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ $movementLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        @if ($adjustment)
                            <a href="{{ route('stock-adjustments.show', $adjustment) }}" class="font-semibold text-slate-700 hover:text-slate-900">{{ $adjustment->adjustment_no }}</a>
                        @elseif ($movement->reference_type === 'sales_return' && $movement->reference_id)
                            <a href="{{ route('sales-returns.show', $movement->reference_id) }}" class="font-semibold text-slate-700 hover:text-slate-900">Sales Return #{{ $movement->reference_id }}</a>
                        @elseif ($movement->reference_type === 'purchase_return' && $movement->reference_id)
                            <a href="{{ route('purchase-returns.show', $movement->reference_id) }}" class="font-semibold text-slate-700 hover:text-slate-900">Purchase Return #{{ $movement->reference_id }}</a>
                        @else
                            {{ ucfirst(str_replace('_', ' ', $movement->reference_type ?? '-')) }}
                            @if ($movement->reference_id)
                                #{{ $movement->reference_id }}
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold {{ $isOut ? 'text-red-700' : ($isIn ? 'text-emerald-700' : 'text-gray-900') }}">
                        {{ $isOut ? '-' : '+' }}{{ number_format((float) $movement->quantity, 3) }}
                    </td>
                    <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $movement->rate, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format((float) $movement->total_value, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $movement->remarks ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">No stock movements found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
