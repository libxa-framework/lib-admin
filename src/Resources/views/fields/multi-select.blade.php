@php
    $name     = $field['name'];
    $label    = $field['label'];
    $required = $field['required'] ?? false;
    $options  = $field['options'] ?? [];
    $hint     = $field['hint'] ?? '';
    $icon     = $field['icon'] ?? 'checklist';
    $searchable = $field['searchable'] ?? false;
    $selected = (array) old($name, $value ?? $field['default'] ?? []);
@endphp

<div class="space-y-1.5" x-data="{ search: '' }">
    <label for="{{ $name }}"
           class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        {{ $label }}@if($required)<span class="text-error ml-1">*</span>@endif
    </label>

    @if($searchable)
        <div class="mb-2">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-[16px]">search</span>
                <input type="text" 
                       x-model="search"
                       placeholder="Filter {{ strtolower($label) }}..."
                       class="w-full bg-surface text-xs border-0 border-b border-outline-variant focus:border-primary focus:ring-0 pl-8 pr-3 py-1.5 transition-all">
            </div>
        </div>
    @endif

    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-3 text-on-surface-variant text-[18px] pointer-events-none">{{ $icon }}</span>
        <select id="{{ $name }}"
                name="{{ $name }}[]"
                multiple
                {{ $required ? 'required' : '' }}
                class="w-full appearance-none bg-surface-container-high border-0 border-b-2 border-transparent
                       focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                       text-on-surface transition-all cursor-default min-h-[120px]
                       @error($name) border-error @enderror">

            @foreach($options as $optVal => $optLabel)
                <option value="{{ $optVal }}" 
                        x-show="search === '' || '{{ strtolower($optLabel) }}'.includes(search.toLowerCase())"
                        {{ in_array($optVal, $selected) ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
        </select>
        <p class="text-[10px] text-on-surface-variant mt-1 italic">Hold Ctrl (Cmd) to select multiple</p>
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
