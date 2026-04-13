{{--
    Textarea field partial
    Expected: $field (viewData array), $value (current value)
--}}
@php
    $name        = $field['name'];
    $label       = $field['label'];
    $required    = $field['required'] ?? false;
    $placeholder = $field['placeholder'] ?? '';
    $rows        = $field['rows'] ?? 4;
    $hint        = $field['hint'] ?? '';
@endphp

<div class="space-y-1.5">
    <label for="{{ $name }}"
           class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        {{ $label }}@if($required)<span class="text-error ml-1">*</span>@endif
    </label>

    <textarea id="{{ $name }}"
              name="{{ $name }}"
              rows="{{ $rows }}"
              placeholder="{{ $placeholder }}"
              {{ $required ? 'required' : '' }}
              class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                     focus:border-primary focus:ring-0 rounded-t-xl px-4 py-3 text-sm
                     text-on-surface placeholder:text-on-surface-variant/50 transition-all resize-none
                     @error($name) border-error @enderror">{{ old($name, $value ?? $field['default'] ?? '') }}</textarea>

    @error($name)
        <p class="flex items-center gap-1 text-xs text-error mt-1">
            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
        </p>
    @enderror

    @if($hint)
        <p class="text-xs text-on-surface-variant">{{ $hint }}</p>
    @endif
</div>
