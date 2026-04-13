<div class="space-y-1.5">
    <label for="{{ $name }}" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
        {{ $label }} @if($required)<span class="text-error">*</span>@endif
    </label>

    <div class="flex flex-col gap-3">
        {{-- Preview Area (if image) --}}
        @if($isImage && isset($item) && $item->{$name})
            <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-surface-container-highest shadow-inner bg-surface">
                <img src="{{ $item->{$name} }}" class="w-full h-full object-cover">
            </div>
        @endif

        {{-- Dropzone styling --}}
        <div class="group relative">
            <input type="file"
                   id="{{ $name }}"
                   name="{{ $name }}"
                   accept="{{ $acceptedTypes }}"
                   @if($required && !isset($item)) required @endif
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

            <div class="w-full flex items-center gap-4 px-4 py-3 bg-surface-container-high
                      rounded-xl border-2 border-dashed border-surface-container-highest
                      group-hover:border-primary/30 group-hover:bg-primary/5 transition-all">
                <div class="p-2 bg-surface-container-lowest text-on-surface-variant rounded-lg">
                    <span class="material-symbols-outlined">{{ $isImage ? 'image' : 'upload_file' }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold text-on-surface">Click to upload or drag and drop</p>
                    <p class="text-[11px] text-on-surface-variant mt-0.5">
                        {{ $isImage ? 'PNG, JPG or GIF up to 5MB' : 'Select a file to upload' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($hint)
        <p class="text-[11px] text-on-surface-variant italic pl-1">{{ $hint }}</p>
    @endif
</div>
