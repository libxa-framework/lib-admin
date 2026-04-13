<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — LibAdmin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "surface": "#f7f9fb",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f4f7",
                        "surface-container-high": "#e1e9ee",
                        "surface-container-highest": "#d9e4ea",
                        "on-surface": "#2a3439",
                        "on-surface-variant": "#566166",
                        "primary": "#0053db",
                        "primary-dim": "#0048c1",
                        "primary-container": "#dbe1ff",
                        "on-primary": "#f8f7ff",
                        "on-primary-container": "#0048bf",
                        "error": "#9f403d",
                        "error-container": "#fe8983",
                        "on-error-container": "#752121",
                    },
                    fontFamily: {
                        headline: ["Manrope", "sans-serif"],
                        body:     ["Inter",   "sans-serif"],
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
    </style>
</head>
<body class="min-h-screen bg-surface font-body text-on-surface antialiased flex items-center justify-center p-4">

    {{-- Decorative blobs --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden -z-10">
        <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-primary/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-[400px] h-[400px] bg-primary-container/60 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md space-y-8">

        {{-- Brand Header --}}
        <div class="flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center shadow-xl shadow-primary/20 mb-5">
                <span class="material-symbols-outlined text-white text-3xl" style="font-variation-settings:'FILL' 1;">shield</span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-on-surface font-headline">Welcome back</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Sign in to your LibAdmin control panel</p>
        </div>

        {{-- Card --}}
        <div class="bg-surface-container-lowest rounded-2xl shadow-[0_16px_48px_-8px_rgba(42,52,57,0.10)] p-8 space-y-6">

            {{-- Error alert --}}
            @if(session('error'))
                <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-sm">
                    <span class="material-symbols-outlined text-error mt-0.5 shrink-0">error</span>
                    <p class="text-on-error-container font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <form method="POST" action="/admin/login" class="space-y-5">

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                        Email Address
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">mail</span>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="email"
                               placeholder="admin@example.com"
                               class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                      focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                      text-on-surface placeholder:text-on-surface-variant/50 transition-all">
                    </div>
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                        Password
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">lock</span>
                        <input type="password" id="password" name="password"
                               required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                      focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                      text-on-surface placeholder:text-on-surface-variant/50 transition-all">
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember"
                           class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/20 cursor-pointer">
                    <label for="remember" class="text-sm text-on-surface-variant cursor-pointer select-none">Remember me</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-3 px-6 bg-gradient-to-r from-primary to-primary-dim text-on-primary font-bold text-sm
                               rounded-xl shadow-lg shadow-primary/20 hover:opacity-90 active:scale-[0.98] transition-all
                               flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">login</span>
                    Sign In
                </button>

            </form>
        </div>

        <p class="text-center text-xs text-on-surface-variant">
            LibAdmin &mdash; NexmFrame Control Panel
        </p>
    </div>
</body>
</html>
