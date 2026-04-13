{{--
    Toggle / checkbox field partial
    Expected: $field (viewData array), $value (bool / 1 / 0)
--}}
@php
    $name    = $field['name'];
    $label   = $field['label'];
    $hint    = $field['hint'] ?? '';
    $checked = old($name, $value ?? $field['default'] ?? false);
    $icon    = $field['icon'] ?? 'toggle_on';
@endphp

<div class="flex items-center justify-between p-4 bg-surface-container-low rounded-xl">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-primary shadow-sm shrink-0">
            <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1;">{{ $icon }}</span>
        </div>
        <div>
            <p class="text-sm font-bold text-on-surface">{{ $label }}</p>
            @if($hint)
                <p class="text-xs text-on-surface-variant">{{ $hint }}</p>
            @endif
        </div>
    </div>
    <label class="relative inline-flex items-center cursor-pointer ml-4 shrink-0">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox"
               id="{{ $name }}"
               name="{{ $name }}"
               value="1"
               {{ $checked ? 'checked' : '' }}
               class="sr-only peer">
        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer
                    peer-checked:after:translate-x-full peer-checked:after:border-white
                    after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                    after:bg-white after:border-slate-300 after:border after:rounded-full
                    after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
    </label>
</div>
