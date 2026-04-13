@php
    $name     = $field['name'];
    $label    = $field['label'];
    $required = $field['required'] ?? false;
    $options  = $field['options'] ?? [];
    $hint     = $field['hint'] ?? '';
    $icon     = $field['icon'] ?? 'link';
    $searchable = $field['searchable'] ?? false;
    $currentValue = old($name, $value ?? $field['default'] ?? '');
@endphp

<div class="space-y-1.5" x-data="{ open: false, search: '', selected: '{{ $currentValue }}' }">
    <label for="{{ $name }}_search"
           class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        {{ $label }}@if($required)<span class="text-error ml-1">*</span>@endif
    </label>

    <div class="relative">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">{{ $icon }}</span>
        
        @if($searchable)
            <div class="relative" id="searchable-{{ $name }}-container">
                <input type="text"
                       id="{{ $name }}_search"
                       placeholder="Select {{ $label }}..."
                       readonly
                       value="{{ $options[$currentValue] ?? '' }}"
                       @click="open = !open"
                       class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                              focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-10 py-3 text-sm
                              text-on-surface transition-all cursor-pointer @error($name) border-error @enderror">
                
                <input type="hidden" name="{{ $name }}" value="{{ $currentValue }}" id="{{ $name }}_hidden">

                <div x-show="open" 
                     @click.away="open = false"
                     class="absolute z-50 w-full mt-1 bg-surface-container-highest rounded-b-xl shadow-2xl border border-outline-variant max-h-60 overflow-y-auto"
                     style="display: none;">
                    
                    <div class="p-2 sticky top-0 bg-surface-container-highest border-b border-outline-variant">
                        <input type="text" 
                               x-model="search"
                               placeholder="Filter options..."
                               class="w-full bg-surface text-sm border-0 focus:ring-1 focus:ring-primary rounded-lg py-1.5 px-3">
                    </div>

                    <ul class="py-1">
                        @if(!$required)
                            <li @click="selected = ''; open = false; document.getElementById('{{ $name }}_hidden').value = ''; document.getElementById('{{ $name }}_search').value = '';"
                                class="px-4 py-2 text-sm text-on-surface-variant hover:bg-primary/10 cursor-pointer italic">
                                — None —
                            </li>
                        @endif
                        
                        @foreach($options as $optVal => $optLabel)
                            <li x-show="'{{ strtolower($optLabel) }}'.includes(search.toLowerCase())"
                                @click="selected = '{{ $optVal }}'; open = false; document.getElementById('{{ $name }}_hidden').value = '{{ $optVal }}'; document.getElementById('{{ $name }}_search').value = '{{ $optLabel }}';"
                                class="px-4 py-2 text-sm text-on-surface hover:bg-primary hover:text-on-primary cursor-pointer transition-colors"
                                :class="{ 'bg-primary/20': selected === '{{ $optVal }}' }">
                                {{ $optLabel }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            <select id="{{ $name }}"
                    name="{{ $name }}"
                    {{ $required ? 'required' : '' }}
                    class="w-full appearance-none bg-surface-container-high border-0 border-b-2 border-transparent
                           focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-10 py-3 text-sm
                           text-on-surface transition-all cursor-pointer
                           @error($name) border-error @enderror">

                @if(!$required)
                    <option value="">— Select —</option>
                @endif

                @foreach($options as $optVal => $optLabel)
                    <option value="{{ $optVal }}" {{ $currentValue == $optVal ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @endif

        <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">expand_more</span>
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
