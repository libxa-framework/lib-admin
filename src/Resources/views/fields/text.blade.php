{{--
    Generic text / email / number / url / tel / date / time / color / range input partial.
    Expected variables:
        $field  = viewData() array from AdminField subclass
        $value  = current value (string|null)
--}}
@php
    $type        = $field['inputType'] ?? 'text';
    $name        = $field['name'];
    $label       = $field['label'];
    $required    = $field['required'] ?? false;
    $placeholder = $field['placeholder'] ?? '';
    $maxLength   = $field['maxLength'] ?? null;
    $hint        = $field['hint'] ?? '';
    $autoComp    = ($field['autoComplete'] ?? true) ? 'on' : 'off';

    $icon = match($name) {
        'email'    => 'mail',
        'password' => 'lock',
        'name'     => 'badge',
        'phone'    => 'phone',
        'url'      => 'link',
        'date'     => 'calendar_today',
        'number'   => 'tag',
        default    => 'edit',
    };
@endphp

<div class="space-y-1.5">
    <label for="{{ $name }}"
           class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        {{ $label }}@if($required)<span class="text-error ml-1">*</span>@endif
    </label>

    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">{{ $icon }}</span>
        <input type="{{ $type }}"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ old($name, $value ?? $field['default'] ?? '') }}"
               placeholder="{{ $placeholder }}"
               autocomplete="{{ $autoComp }}"
               {{ $required ? 'required' : '' }}
               {{ $maxLength ? 'maxlength='.$maxLength : '' }}
               class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                      focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                      text-on-surface placeholder:text-on-surface-variant/50 transition-all
                      @error($name) border-error @enderror">
    </div>

    @error($name)
        <p class="flex items-center gap-1 text-xs text-error mt-1">
            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
        </p>
    @enderror

    @if($hint)
        <p class="text-xs text-on-surface-variant">{{ $hint }}</p>
    @endif
</div>
