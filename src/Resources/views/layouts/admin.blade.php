<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin — LibAdmin')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f4f7",
                        "surface-container": "#e8eff3",
                        "surface-container-high": "#e1e9ee",
                        "surface-container-highest": "#d9e4ea",
                        "surface-variant": "#d9e4ea",
                        "surface-dim": "#cfdce3",
                        "surface-bright": "#f7f9fb",
                        "surface": "#f7f9fb",
                        "background": "#f7f9fb",
                        "on-surface": "#2a3439",
                        "on-surface-variant": "#566166",
                        "on-background": "#2a3439",
                        "primary": "#0053db",
                        "primary-dim": "#0048c1",
                        "primary-container": "#dbe1ff",
                        "primary-fixed": "#dbe1ff",
                        "primary-fixed-dim": "#c7d3ff",
                        "on-primary": "#f8f7ff",
                        "on-primary-container": "#0048bf",
                        "on-primary-fixed": "#003798",
                        "on-primary-fixed-variant": "#0050d4",
                        "inverse-primary": "#618bff",
                        "secondary": "#506076",
                        "secondary-dim": "#44546a",
                        "secondary-container": "#d3e4fe",
                        "secondary-fixed": "#d3e4fe",
                        "secondary-fixed-dim": "#c5d6f0",
                        "on-secondary": "#f7f9ff",
                        "on-secondary-container": "#435368",
                        "on-secondary-fixed": "#314055",
                        "on-secondary-fixed-variant": "#4d5d73",
                        "tertiary": "#605c78",
                        "tertiary-dim": "#54506b",
                        "tertiary-container": "#e3dbfd",
                        "tertiary-fixed": "#e3dbfd",
                        "tertiary-fixed-dim": "#d4cdee",
                        "on-tertiary": "#fcf7ff",
                        "on-tertiary-container": "#514d68",
                        "on-tertiary-fixed": "#3e3a54",
                        "on-tertiary-fixed-variant": "#5b5672",
                        "error": "#9f403d",
                        "error-dim": "#4e0309",
                        "error-container": "#fe8983",
                        "on-error": "#fff7f6",
                        "on-error-container": "#752121",
                        "outline": "#717c82",
                        "outline-variant": "#a9b4b9",
                        "surface-tint": "#0053db",
                        "inverse-surface": "#0b0f10",
                        "inverse-on-surface": "#9a9d9f",
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        "2xl": "1rem",
                        full: "9999px",
                    },
                    fontFamily: {
                        headline: ["Manrope", "sans-serif"],
                        body: ["Inter", "sans-serif"],
                        label: ["Inter", "sans-serif"],
                    },
                },
            },
        };
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d9e4ea; border-radius: 10px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-surface font-body text-on-surface antialiased">

<!-- ===================== SIDEBAR ===================== -->
<aside id="sidebar" class="h-screen w-64 fixed left-0 top-0 overflow-y-auto bg-slate-50 flex flex-col p-4 z-50 transition-all duration-300">

    {{-- Brand --}}
    <div class="px-2 py-5 mb-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-white" style="font-variation-settings:'FILL' 1;">shield</span>
        </div>
        <div>
            <h1 class="text-base font-extrabold text-slate-900 font-headline leading-tight">LibAdmin</h1>
            <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Control Panel</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-0.5">
        @php
            // Always ensure Dashboard is first
            $navItems = array_merge(
                [['icon' => 'dashboard', 'label' => 'Dashboard', 'href' => '/admin/dashboard', 'group' => 'General']],
                \Libxa\Admin\Facades\Admin::getNavigation()
            );
            $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
            
            // Group items
            $groupedNav = [];
            foreach ($navItems as $item) {
                $group = $item['group'] ?? 'General';
                $groupedNav[$group][] = $item;
            }
        @endphp

        @foreach($groupedNav as $group => $items)
            @if(count($groupedNav) > 1 && $group !== 'General')
                <div class="px-3 pt-4 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $group }}</p>
                </div>
            @endif
            @foreach($items as $item)
                @php $active = str_starts_with($current, parse_url($item['href'], PHP_URL_PATH) ?? ''); @endphp
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold font-headline tracking-wide transition-all
                          {{ $active
                              ? 'text-blue-700 bg-blue-50/60'
                              : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50' }}">
                    <span class="material-symbols-outlined" style="{{ $active ? "font-variation-settings:'FILL' 1;" : '' }}">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        @endforeach
    </nav>

    {{-- Custom view injected via hook --}}
    {!! \Libxa\Admin\Facades\Admin::renderHook('sidebar.footer') !!}
</aside>

<!-- ===================== MAIN ===================== -->
<main class="pl-64 min-h-screen flex flex-col">

    {{-- Top Navigation Bar --}}
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200/50 shadow-sm flex items-center justify-between px-8 h-16 w-full">
        <div class="flex items-center gap-6">
            <h2 class="text-xl font-bold tracking-tight text-slate-900 font-headline">@yield('header', 'Dashboard')</h2>
            <div class="relative hidden md:block">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                <input type="text" placeholder="Search..."
                       class="bg-surface-container-low border-none rounded-full py-1.5 pl-9 pr-4 text-xs w-56 focus:ring-1 focus:ring-primary/20 transition-all" />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button class="relative p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                <span class="material-symbols-outlined">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full border-2 border-white"></span>
            </button>
            <div class="h-8 w-px bg-slate-200 mx-1"></div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings:'FILL' 1;">person</span>
                </div>
                <div class="hidden lg:block text-left">
                    <p class="text-[13px] font-semibold text-slate-900 leading-none">
                        @if(isset($user) && $user){{ $user->name ?? 'Admin' }}@else Admin @endif
                    </p>
                    <p class="text-[11px] text-slate-500 leading-tight">Administrator</p>
                </div>
            </div>
            <form action="/admin/logout" method="POST" class="ml-2">
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-error bg-error-container/20 hover:bg-error-container/40 transition-colors">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    Logout
                </button>
            </form>
        </div>
    </header>

    {{-- Page Content --}}
    <div class="p-8 flex-1 space-y-6">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-semibold">
                <span class="material-symbols-outlined text-emerald-500">check_circle</span>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-semibold">
                <span class="material-symbols-outlined text-red-500">error</span>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="mt-auto px-8 py-4 bg-surface-container-high/40 border-t border-slate-200/50 flex justify-between items-center text-[10px] font-bold text-on-surface-variant uppercase tracking-[0.15em]">
        <span>System Status: Operational</span>
        <span>LibAdmin &mdash; NexmFrame</span>
        <span>v1.0.0</span>
    </footer>
</main>

</body>
</html>
