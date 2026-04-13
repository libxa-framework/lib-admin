{{--
    Select / dropdown field partial
    Expected: $field (viewData array with 'options' key), $value (currently selected value)
--}}
@php
    $name     = $field['name'];
    $label    = $field['label'];
    $required = $field['required'] ?? false;
    $options  = $field['options'] ?? [];
    $hint     = $field['hint'] ?? '';
    $icon     = $field['icon'] ?? 'unfold_more';
    $searchable = $field['searchable'] ?? false;
    $id = $name . '_' . uniqid();
@endphp

    <div class="relative group" x-data="{ open: false, search: '', selected: '{{ old($name, $value ?? $field['default'] ?? '') }}' }">
        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none z-10">{{ $icon }}</span>
        
        @if($searchable)
            <div class="relative">
                <select id="{{ $id }}"
                        name="{{ $name }}"
                        {{ $required ? 'required' : '' }}
                        class="w-full appearance-none bg-surface-container-high border-0 border-b-2 border-transparent
                               focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-10 py-3 text-sm
                               text-on-surface transition-all cursor-pointer
                               @error($name) border-error @enderror"
                        onfocus="this.size=8;" onblur="this.size=1;" onchange="this.size=1; this.blur();">

                    @if(!$required)
                        <option value="">— Select —</option>
                    @endif

                    @foreach($options as $optVal => $optLabel)
                        <option value="{{ $optVal }}" {{ old($name, $value ?? $field['default'] ?? '') == $optVal ? 'selected' : '' }}>
                            {{ $optLabel }}
                        </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">search</span>
                
                <script>
                    (function() {
                        const select = document.getElementById('{{ $id }}');
                        const originalOptions = Array.from(select.options);
                        
                        // For a truly premium feel without Alpine, we could add a real filter input.
                        // But for now, we'll use a trick: standard select with autofocus search if possible,
                        // or just keep it simple.
                    })();
                </script>
            </div>
        @else
            <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px] pointer-events-none">unfold_more</span>
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
                    <option value="{{ $optVal }}" {{ old($name, $value ?? $field['default'] ?? '') == $optVal ? 'selected' : '' }}>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @endif
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
