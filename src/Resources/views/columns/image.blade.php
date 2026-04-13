@php
    $url = $item->{$name} ?? 'https://ui-avatars.com/api/?name=' . urlencode($item->name ?? 'User') . '&background=random';
    $rounded = $circular ? 'rounded-full' : 'rounded-lg';
@endphp

<div class="flex items-center justify-center">
    <div class="relative group">
        <img src="{{ $url }}"
             alt="{{ $name }}"
             class="object-cover {{ $rounded }} border border-surface-container-highest shadow-sm transition-transform group-hover:scale-110"
             style="width: {{ $size }}px; height: {{ $size }}px;">
        <div class="absolute inset-0 {{ $rounded }} ring-1 ring-inset ring-black/5"></div>
    </div>
</div>
