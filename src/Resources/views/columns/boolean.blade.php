<div class="flex items-center justify-center">
    @if($item->{$name})
        <div class="p-1 px-2 bg-emerald-50 text-emerald-600 rounded-full flex items-center gap-1 border border-emerald-100">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">{{ $trueIcon }}</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Active</span>
        </div>
    @else
        <div class="p-1 px-2 bg-red-50 text-red-600 rounded-full flex items-center gap-1 border border-red-100">
            <span class="material-symbols-outlined text-[18px]" style="font-variation-settings:'FILL' 1;">{{ $falseIcon }}</span>
            <span class="text-[10px] font-bold uppercase tracking-wider">Inactive</span>
        </div>
    @endif
</div>
