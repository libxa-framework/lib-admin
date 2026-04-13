@php
    $value = $item->{$name};
    $color = $colors[$value] ?? 'slate';
    $label = ucfirst($value);
@endphp

<div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
            bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200 shadow-sm shadow-{{ $color }}-100/50">
    {{ $label }}
</div>
