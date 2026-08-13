{{-- BooleanColumn cell. --}}
@php
    $value = \Libxa\Admin\Columns\AdminColumn::valueFor($column, $item);

    // "0" and "" arrive as strings from the database, and both are false.
    $on = filter_var($value, FILTER_VALIDATE_BOOL);
@endphp

<td class="px-6 py-4 text-sm">
    <div class="flex items-center">
        @if($on)
            <div class="p-1 px-2 bg-emerald-50 text-emerald-600 rounded-full flex items-center gap-1 border border-emerald-100">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">{{ $column['trueIcon'] ?? 'check_circle' }}</span>
                <span class="text-[10px] font-bold uppercase tracking-wider">Active</span>
            </div>
        @else
            <div class="p-1 px-2 bg-red-50 text-red-600 rounded-full flex items-center gap-1 border border-red-100">
                <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">{{ $column['falseIcon'] ?? 'cancel' }}</span>
                <span class="text-[10px] font-bold uppercase tracking-wider">Inactive</span>
            </div>
        @endif
    </div>
</td>
