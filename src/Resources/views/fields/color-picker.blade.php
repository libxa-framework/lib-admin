@php
    $id = $field['name'];
    $label = $field['label'];
    $required = $field['required'] ?? false;
    $hint = $field['hint'] ?? '';
    $placeholder = $field['placeholder'] ?? '';
    $value = old($field['name'], $value ?? $field['default'] ?? '');
@endphp

<div class="space-y-1.5">
    <label for="{{ $id }}" class="block text-sm font-semibold text-on-surface-variant tracking-tight">
        {{ $label }}
        @if($required)
            <span class="text-error font-bold">*</span>
        @endif
    </label>

    <div class="relative flex items-center gap-3">
        <input 
            type="color" 
            id="{{ $id }}" 
            name="{{ $field['name'] }}" 
            value="{{ $value }}"
            @if($required) required @endif
            class="w-12 h-10 p-0 border-0 bg-transparent cursor-pointer overflow-hidden rounded-lg shadow-sm focus:ring-2 focus:ring-primary transition-all duration-200"
        >
        
        <input 
            type="text" 
            readonly 
            value="{{ $value }}"
            class="flex-1 bg-surface-container-high border-0 text-on-surface text-sm rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-primary transition-all duration-200 font-mono uppercase"
        >
    </div>

    @if($hint)
        <p class="text-xs text-on-surface-variant/70 italic px-1">{{ $hint }}</p>
    @endif

    @error($field['name'])
        <p class="text-xs text-error font-medium flex items-center gap-1.5 px-1 pt-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            {{ $message }}
        </p>
    @enderror
</div>
