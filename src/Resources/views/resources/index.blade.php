@extends('admin::layouts.admin')

@section('title'){{ ucfirst($resource) }} — LibAdmin@endsection

@section('header'){{ ucfirst($resource) }}@endsection

@section('content')

{{-- Page heading & action bar --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface font-headline tracking-tight">{{ ucfirst($resource) }} Ledger</h2>
        <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-1">
            <a href="/admin/dashboard" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-semibold text-primary">{{ ucfirst($resource) }}</span>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/resources/{{ $resource }}/create"
           class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-br from-primary to-primary-dim text-on-primary
                  text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 transition-all active:scale-95">
            <span class="material-symbols-outlined text-lg">add</span>
            New {{ ucfirst(rtrim($resource, 's')) }}
        </a>
    </div>
</div>

{{-- Header Widgets --}}
@if(isset($headerWidgets) && count($headerWidgets) > 0)
    <div class="grid grid-cols-12 gap-5 mb-5 mt-5">
        @foreach($headerWidgets as $widget)
            <div class="col-span-12 lg:col-span-{{ $widget->getColumnSpan() }}">
                {!! $widget->render() !!}
            </div>
        @endforeach
    </div>
@endif

{{-- Table Container --}}
<div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden border border-slate-100">

    {{-- Filters row --}}
    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/60">
        <div class="flex items-center gap-2">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
                <input type="text" placeholder="Search records…"
                       class="bg-white border border-slate-200 text-xs font-semibold rounded-lg pl-9 pr-4 py-2 focus:ring-1 focus:ring-primary outline-none text-on-surface w-52">
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">filter_list</span>
                <select class="appearance-none bg-white border border-slate-200 text-xs font-semibold rounded-lg pl-9 pr-6 py-2 focus:ring-1 focus:ring-primary outline-none text-on-surface cursor-pointer">
                    <option>All Records</option>
                </select>
            </div>
        </div>
        <p class="text-xs font-medium text-on-surface-variant">
            @if(isset($items) && $items)
                Showing <span class="text-on-surface font-bold">{{ count($items) }}</span> record(s)
            @endif
        </p>
    </div>

    {{-- Data table --}}
    @if(isset($items) && $items && count($items) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low/50">
                        @php $columns = isset($columnDefs) ? $columnDefs : array_keys((array) $items[0]); @endphp

                        @if(isset($columnDefs))
                            @foreach($columnDefs as $col)
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">
                                    <div class="flex items-center gap-1">
                                        {{ $col['label'] }}
                                        @if($col['sortable'] ?? false)
                                            <span class="material-symbols-outlined text-xs opacity-40">unfold_more</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        @else
                            @foreach($columns as $col)
                                <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant">
                                    {{ ucfirst(str_replace('_', ' ', $col)) }}
                                </th>
                            @endforeach
                        @endif

                        <th class="px-6 py-4 text-[11px] font-bold uppercase tracking-wider text-on-surface-variant text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @foreach($items as $item)
                        <tr class="group hover:bg-primary-container/20 transition-all cursor-pointer">

                            @if(isset($columnDefs))
                                @foreach($columnDefs as $col)
                                    <td class="px-6 py-4 text-sm text-on-surface-variant">
                                        @php
                                            $val = $item->{$col['name']} ?? '-';
                                            if (($col['limit'] ?? 0) > 0 && strlen($val) > $col['limit']) {
                                                $val = substr($val, 0, $col['limit']) . '…';
                                            }
                                        @endphp
                                        <div class="flex items-center gap-2">
                                            <span class="{{ $col['wrap'] ?? false ? 'whitespace-normal' : 'whitespace-nowrap' }}">{{ $val }}</span>
                                            @if($col['copyable'] ?? false)
                                                <button onclick="navigator.clipboard.writeText('{{ $item->{$col['name']} ?? '' }}')"
                                                        class="opacity-0 group-hover:opacity-100 transition-opacity p-0.5 hover:text-primary">
                                                    <span class="material-symbols-outlined text-xs">content_copy</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            @else
                                @foreach($columns as $col)
                                    <td class="px-6 py-4 text-sm text-on-surface-variant whitespace-nowrap">{{ $item->$col ?? '-' }}</td>
                                @endforeach
                            @endif

                            {{-- Action column --}}
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="/admin/resources/{{ $resource }}/{{ $item->id }}"
                                       class="p-2 rounded-lg hover:bg-white text-on-surface-variant hover:text-primary transition-colors"
                                       title="View">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </a>
                                    <a href="/admin/resources/{{ $resource }}/{{ $item->id }}/edit"
                                       class="p-2 rounded-lg hover:bg-white text-on-surface-variant hover:text-amber-600 transition-colors"
                                       title="Edit">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <form action="/admin/resources/{{ $resource }}/{{ $item->id }}" method="POST" class="inline">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit"
                                                onclick="return confirm('Delete this {{ rtrim($resource, 's') }}? This cannot be undone.')"
                                                class="p-2 rounded-lg hover:bg-white text-on-surface-variant hover:text-error transition-colors"
                                                title="Delete">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination shell --}}
        <div class="px-6 py-5 border-t border-slate-100 flex items-center justify-between">
            <button disabled class="flex items-center gap-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors disabled:opacity-30">
                <span class="material-symbols-outlined text-base">arrow_back</span> Previous
            </button>
            <div class="flex items-center gap-1">
                <button class="w-8 h-8 rounded-lg bg-primary text-on-primary text-xs font-bold shadow-sm">1</button>
            </div>
            <button class="flex items-center gap-2 text-xs font-bold text-on-surface-variant hover:text-primary transition-colors">
                Next <span class="material-symbols-outlined text-base">arrow_forward</span>
            </button>
        </div>

    @else
        {{-- Empty state --}}
        <div class="py-20 flex flex-col items-center text-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-surface-container-high flex items-center justify-center">
                <span class="material-symbols-outlined text-on-surface-variant text-3xl">inbox</span>
            </div>
            <div>
                <p class="font-bold text-on-surface">No {{ $resource }} found</p>
                <p class="text-sm text-on-surface-variant mt-1">Create your first record to get started.</p>
            </div>
            <a href="/admin/resources/{{ $resource }}/create"
               class="mt-2 flex items-center gap-2 px-5 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 transition-all">
                <span class="material-symbols-outlined text-sm">add</span>
                Create First {{ ucfirst(rtrim($resource, 's')) }}
            </a>
        </div>
    @endif
</div>

{{-- Footer Widgets --}}
@if(isset($footerWidgets) && count($footerWidgets) > 0)
    <div class="grid grid-cols-12 gap-5 mt-5">
        @foreach($footerWidgets as $widget)
            <div class="col-span-12 lg:col-span-{{ $widget->getColumnSpan() }}">
                {!! $widget->render() !!}
            </div>
        @endforeach
    </div>
@endif

@endsection
