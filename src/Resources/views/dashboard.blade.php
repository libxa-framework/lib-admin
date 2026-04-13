@extends('admin::layouts.admin')

@section('title', 'Dashboard — LibAdmin')

@section('header', 'Dashboard')

@section('content')

{{-- Page heading --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-2">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface font-headline tracking-tight">Executive Overview</h2>
        <p class="text-on-surface-variant mt-1 text-sm">Welcome back — here's what's happening across your platform.</p>
    </div>
    <div class="flex gap-3">
        <button class="px-4 py-2 bg-surface-container-highest text-on-surface-variant rounded-lg font-semibold text-sm hover:bg-surface-variant transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">calendar_today</span>
            Last 30 Days
        </button>
        <button class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold text-sm shadow-lg shadow-primary/20 hover:opacity-90 transition-all flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">download</span>
            Export
        </button>
    </div>
</div>

{{-- Widgets Grid --}}
<div class="grid grid-cols-12 gap-5">
    @if(isset($widgets) && count($widgets) > 0)
        @foreach($widgets as $widget)
            <div class="col-span-12 lg:col-span-{{ $widget->getColumnSpan() }}">
                {!! $widget->render() !!}
            </div>
        @endforeach
    @else
        <div class="col-span-12 p-8 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center text-slate-400">
            <p>No widgets registered. Register them via the Admin panel.</p>
        </div>
    @endif
</div>

{{-- Bottom section --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-2">

    {{-- Quick Links (left 2/3) --}}
    <div class="xl:col-span-2 bg-surface-container-lowest rounded-2xl p-8 shadow-sm">
        <h3 class="text-lg font-bold font-headline text-on-surface mb-6">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="/admin/resources/users"
               class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl hover:bg-primary-container/30 transition-colors group border border-slate-100">
                <div class="p-3 bg-primary-container text-primary rounded-xl">
                    <span class="material-symbols-outlined">manage_accounts</span>
                </div>
                <div>
                    <p class="font-bold text-sm text-on-surface group-hover:text-primary transition-colors">Manage Users</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">View, create &amp; edit user records</p>
                </div>
            </a>
            <a href="#"
               class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl hover:bg-secondary-container/30 transition-colors group border border-slate-100">
                <div class="p-3 bg-secondary-container text-secondary rounded-xl">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <div>
                    <p class="font-bold text-sm text-on-surface group-hover:text-secondary transition-colors">View Reports</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Access system analytics</p>
                </div>
            </a>
            <a href="#"
               class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl hover:bg-tertiary-container/30 transition-colors group border border-slate-100">
                <div class="p-3 bg-tertiary-container text-tertiary rounded-xl">
                    <span class="material-symbols-outlined">settings</span>
                </div>
                <div>
                    <p class="font-bold text-sm text-on-surface group-hover:text-tertiary transition-colors">Settings</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Configure system preferences</p>
                </div>
            </a>
            <a href="#"
               class="flex items-center gap-4 p-4 bg-surface-container-low rounded-xl hover:bg-surface-variant/60 transition-colors group border border-slate-100">
                <div class="p-3 bg-surface-variant text-on-surface-variant rounded-xl">
                    <span class="material-symbols-outlined">monitoring</span>
                </div>
                <div>
                    <p class="font-bold text-sm text-on-surface transition-colors">Analytics</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Deep-dive performance data</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Live Intelligence (right 1/3) --}}
    <div class="bg-surface-container-low rounded-2xl p-8 flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold font-headline text-on-surface">System Status</h3>
            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
        </div>
        <div class="space-y-4 flex-1">
            @foreach([
                ['icon' => 'check_circle', 'label' => 'Database', 'status' => 'Operational', 'color' => 'emerald'],
                ['icon' => 'check_circle', 'label' => 'Cache',    'status' => 'Operational', 'color' => 'emerald'],
                ['icon' => 'check_circle', 'label' => 'Queue',    'status' => 'Running',     'color' => 'emerald'],
                ['icon' => 'schedule',     'label' => 'Cron',     'status' => 'Scheduled',   'color' => 'amber'],
            ] as $svc)
                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-{{ $svc['color'] }}-500 text-sm"
                              style="font-variation-settings:'FILL' 1;">{{ $svc['icon'] }}</span>
                        <span class="text-sm font-semibold text-on-surface">{{ $svc['label'] }}</span>
                    </div>
                    <span class="text-xs font-bold text-{{ $svc['color'] }}-600 bg-{{ $svc['color'] }}-50 px-2 py-0.5 rounded-full">
                        {{ $svc['status'] }}
                    </span>
                </div>
            @endforeach
        </div>
        <p class="mt-6 text-[11px] text-on-surface-variant text-center">Last checked just now</p>
    </div>
</div>

@endsection
