@extends('admin::layouts.admin')

@section('title'){{ ucfirst($resource) }} #{{ $item->id ?? '?' }} — LibAdmin@endsection

@section('header'){{ ucfirst($resource) }} Details@endsection

@section('content')

@if($item)

{{-- Heading & actions --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface font-headline tracking-tight">
            {{ ucfirst(rtrim($resource, 's')) }} <span class="text-on-surface-variant">#{{ $item->id }}</span>
        </h2>
        <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-1">
            <a href="/admin/dashboard" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <a href="/admin/resources/{{ $resource }}" class="hover:text-primary transition-colors">{{ ucfirst($resource) }}</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-semibold text-primary">#{{ $item->id }}</span>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="/admin/resources/{{ $resource }}/{{ $item->id }}/edit"
           class="flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-bold rounded-xl
                  shadow-lg shadow-amber-500/20 hover:opacity-90 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-sm">edit</span>
            Edit
        </a>
        <a href="/admin/resources/{{ $resource }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-surface-container-highest text-on-surface-variant
                  text-sm font-semibold rounded-xl hover:bg-surface-variant transition-all">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-8 max-w-5xl">

    {{-- Main detail card --}}
    <div class="col-span-12 lg:col-span-8">
        <div class="bg-surface-container-lowest rounded-2xl shadow-[0_12px_32px_-4px_rgba(42,52,57,0.06)] overflow-hidden">
            <div class="flex items-center gap-2 px-8 py-6 border-b border-slate-100">
                <span class="w-1.5 h-6 bg-secondary rounded-full"></span>
                <h3 class="text-lg font-bold tracking-tight font-headline">Record Data</h3>
            </div>

            <div class="divide-y divide-slate-50">
                @php $properties = (array) $item; @endphp

                @foreach($properties as $key => $value)
                    <div class="flex items-start gap-4 px-8 py-5 hover:bg-surface-container-low/40 transition-colors group">
                        <div class="w-40 shrink-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                            </p>
                        </div>
                        <div class="flex-1 flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-on-surface break-all">
                                {{ $key === 'password' ? '••••••••' : ($value ?? '—') }}
                            </p>
                            @if($key !== 'password' && $value)
                                <button onclick="navigator.clipboard.writeText('{{ addslashes($value) }}')"
                                        class="opacity-0 group-hover:opacity-100 shrink-0 p-1.5 rounded-lg hover:bg-surface-container transition-all text-on-surface-variant hover:text-primary">
                                    <span class="material-symbols-outlined text-sm">content_copy</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sidebar actions --}}
    <div class="col-span-12 lg:col-span-4 space-y-6">

        {{-- Quick Actions --}}
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_12px_32px_-4px_rgba(42,52,57,0.06)]">
            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-4">Quick Actions</p>
            <div class="space-y-2">
                <a href="/admin/resources/{{ $resource }}/{{ $item->id }}/edit"
                   class="flex items-center gap-3 w-full px-4 py-3 bg-surface-container-low rounded-xl
                          hover:bg-primary-container/30 transition-colors group text-sm font-semibold text-on-surface">
                    <span class="material-symbols-outlined text-amber-500 text-lg">edit</span>
                    Edit this record
                    <span class="material-symbols-outlined text-on-surface-variant text-sm ml-auto group-hover:text-primary">chevron_right</span>
                </a>
                <a href="/admin/resources/{{ $resource }}/create"
                   class="flex items-center gap-3 w-full px-4 py-3 bg-surface-container-low rounded-xl
                          hover:bg-primary-container/30 transition-colors group text-sm font-semibold text-on-surface">
                    <span class="material-symbols-outlined text-primary text-lg">add_circle</span>
                    Create new record
                    <span class="material-symbols-outlined text-on-surface-variant text-sm ml-auto group-hover:text-primary">chevron_right</span>
                </a>
                <a href="/admin/resources/{{ $resource }}"
                   class="flex items-center gap-3 w-full px-4 py-3 bg-surface-container-low rounded-xl
                          hover:bg-primary-container/30 transition-colors group text-sm font-semibold text-on-surface">
                    <span class="material-symbols-outlined text-secondary text-lg">table_rows</span>
                    All {{ ucfirst($resource) }}
                    <span class="material-symbols-outlined text-on-surface-variant text-sm ml-auto group-hover:text-primary">chevron_right</span>
                </a>
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
        <p class="text-sm text-on-surface-variant mt-1">The record you're looking for doesn't exist or was deleted.</p>
    </div>
    <a href="/admin/resources/{{ $resource }}"
       class="mt-2 flex items-center gap-2 px-5 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 transition-all">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        Back to {{ ucfirst($resource) }}
    </a>
</div>

@endif

@endsection
