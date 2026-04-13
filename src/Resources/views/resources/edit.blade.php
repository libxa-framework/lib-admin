@extends('admin::layouts.admin')

@section('title')Edit {{ ucfirst($resource) }} — LibAdmin@endsection

@section('header')Edit {{ ucfirst($resource) }}@endsection

@section('content')

@if($item)

{{-- Heading & breadcrumbs --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface font-headline tracking-tight">Edit {{ ucfirst(rtrim($resource, 's')) }}</h2>
        <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-1">
            <a href="/admin/dashboard" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <a href="/admin/resources/{{ $resource }}" class="hover:text-primary transition-colors">{{ ucfirst($resource) }}</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-semibold text-primary">#{{ $item->id }}</span>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="/admin/resources/{{ $resource }}/{{ $item->id }}"
           class="flex items-center gap-2 px-4 py-2 bg-surface-container-highest text-on-surface-variant text-sm font-semibold rounded-xl hover:bg-surface-variant transition-all">
            <span class="material-symbols-outlined text-sm">visibility</span> View
        </a>
        <a href="/admin/resources/{{ $resource }}"
           class="px-5 py-2 rounded-xl text-sm font-semibold text-on-secondary-container bg-secondary-container hover:bg-slate-200 transition-all">
            Cancel
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-8 max-w-5xl">

    {{-- Main Form --}}
    <div class="col-span-12 lg:col-span-8">
        <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_method" value="PUT">

            <section class="bg-surface-container-lowest p-8 rounded-2xl shadow-[0_12px_32px_-4px_rgba(42,52,57,0.06)] space-y-6">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-1.5 h-6 bg-amber-400 rounded-full"></span>
                    <h3 class="text-lg font-bold tracking-tight font-headline">Edit Details</h3>
                </div>

                @if(isset($fields) && count($fields))

                    @foreach($fields as $field)
                        <?php echo app('blade')->render('admin::fields.' . ($field['type'] ?? 'text'), array_merge(get_defined_vars(), [
                            'field' => $field,
                            'value' => $item->{$field['name']} ?? ($field['default'] ?? '')
                        ])); ?>
                    @endforeach

                @else
                    {{-- Generic fallback using item properties --}}
                    @php $properties = (array) $item; @endphp

                    @foreach($properties as $key => $value)
                        @if($key !== 'id' && $key !== 'password')
                            <div class="space-y-1.5">
                                <label for="{{ $key }}" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">
                                        {{ $key === 'email' ? 'mail' : ($key === 'name' ? 'badge' : 'edit') }}
                                    </span>
                                    @if($key === 'email')
                                        <input type="email" id="{{ $key }}" name="{{ $key }}" value="{{ $value ?? '' }}" required
                                               class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                                      focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                                      text-on-surface transition-all">
                                    @elseif($key === 'role')
                                        <select id="{{ $key }}" name="{{ $key }}"
                                                class="w-full appearance-none bg-surface-container-high border-0 border-b-2 border-transparent
                                                       focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                                       text-on-surface transition-all cursor-pointer">
                                            <option value="admin"  {{ $value === 'admin'  ? 'selected' : '' }}>Admin</option>
                                            <option value="editor" {{ $value === 'editor' ? 'selected' : '' }}>Editor</option>
                                            <option value="viewer" {{ $value === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                        </select>
                                    @else
                                        <input type="text" id="{{ $key }}" name="{{ $key }}" value="{{ $value ?? '' }}"
                                               class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                                      focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                                      text-on-surface transition-all">
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Password (optional) --}}
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            New Password <span class="text-on-surface-variant normal-case font-normal">(leave blank to keep)</span>
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">lock</span>
                            <input type="password" id="password" name="password"
                                   placeholder="••••••••"
                                   class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                          focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                          text-on-surface placeholder:text-on-surface-variant/50 transition-all">
                        </div>
                    </div>
                @endif

                {{-- Submit --}}
                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600
                                   text-white text-sm font-bold rounded-xl shadow-lg shadow-amber-500/20
                                   hover:opacity-90 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">save</span>
                        Save Changes
                    </button>
                    <a href="/admin/resources/{{ $resource }}"
                       class="flex items-center px-5 py-2.5 bg-surface-container-highest text-on-surface-variant
                              text-sm font-semibold rounded-xl hover:bg-surface-variant transition-all">
                        Cancel
                    </a>
                </div>
            </section>
        </form>
    </div>

    {{-- Meta Sidebar --}}
    <div class="col-span-12 lg:col-span-4 space-y-6">
        {{-- Record ID chip --}}
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_12px_32px_-4px_rgba(42,52,57,0.06)]">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3">Record Info</p>
            <div class="flex items-center gap-3 p-3 bg-surface-container-low rounded-xl">
                <div class="w-10 h-10 rounded-xl bg-primary-container flex items-center justify-center text-primary font-extrabold text-sm font-headline">
                    #{{ $item->id }}
                </div>
                <div>
                    <p class="text-sm font-bold text-on-surface">{{ ucfirst(rtrim($resource, 's')) }} Record</p>
                    <p class="text-[11px] text-on-surface-variant">ID {{ $item->id }}</p>
                </div>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="bg-red-50 border border-red-100 p-6 rounded-2xl">
            <p class="text-xs font-bold uppercase tracking-wider text-error mb-3">Danger Zone</p>
            <p class="text-xs text-on-surface-variant mb-4">Deleting this record is permanent and cannot be undone.</p>
            <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        onclick="return confirm('Permanently delete this {{ rtrim($resource, 's') }}?')"
                        class="w-full flex items-center justify-center gap-2 py-2 bg-error text-on-error
                               text-xs font-bold rounded-xl hover:opacity-90 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-sm">delete_forever</span>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

@else

<div class="flex flex-col items-center justify-center py-24 text-center gap-4">
    <div class="w-16 h-16 rounded-2xl bg-surface-container-high flex items-center justify-center">
        <span class="material-symbols-outlined text-on-surface-variant text-3xl">search_off</span>
    </div>
    <div>
        <p class="font-bold text-on-surface">{{ ucfirst($resource) }} not found</p>
        <p class="text-sm text-on-surface-variant mt-1">The record you're trying to edit doesn't exist.</p>
    </div>
    <a href="/admin/resources/{{ $resource }}"
       class="mt-2 flex items-center gap-2 px-5 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 transition-all">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Back to {{ ucfirst($resource) }}
    </a>
</div>

@endif

@endsection
