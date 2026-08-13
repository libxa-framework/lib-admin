{{-- ImageColumn cell. --}}
@php
    $value = \Libxa\Admin\Columns\AdminColumn::valueFor($column, $item);

    $size = $column['size'] ?? 40;
    $rounded = ($column['circular'] ?? false) ? 'rounded-full' : 'rounded-lg';
@endphp

<td class="px-6 py-4 text-sm">
    <div class="flex items-center">
        @if($value)
            <div class="relative group">
                <img src="{{ $value }}"
                     alt="{{ $column['label'] ?? $column['name'] }}"
                     loading="lazy"
                     class="object-cover {{ $rounded }} border border-surface-container-highest shadow-sm transition-transform group-hover:scale-110"
                     style="width: {{ $size }}px; height: {{ $size }}px;">
                <div class="absolute inset-0 {{ $rounded }} ring-1 ring-inset ring-black/5"></div>
            </div>
        @else
            {{-- No placeholder from a third-party avatar service: that would
                 leak a record's name to an external host on every page view. --}}
            <div class="flex items-center justify-center bg-surface-container-high text-on-surface-variant {{ $rounded }}"
                 style="width: {{ $size }}px; height: {{ $size }}px;">
                <span class="material-symbols-outlined text-base">image_not_supported</span>
            </div>
        @endif
    </div>
</td>
