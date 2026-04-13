@extends('admin::layouts.admin')

@section('title')Create {{ ucfirst($resource) }} — LibAdmin@endsection

@section('header')Create {{ ucfirst($resource) }}@endsection

@section('content')

{{-- Heading & actions --}}
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-extrabold text-on-surface font-headline tracking-tight">Create {{ ucfirst(rtrim($resource, 's')) }}</h2>
        <div class="flex items-center gap-2 text-on-surface-variant text-sm mt-1">
            <a href="/admin/dashboard" class="hover:text-primary transition-colors">Dashboard</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <a href="/admin/resources/{{ $resource }}" class="hover:text-primary transition-colors">{{ ucfirst($resource) }}</a>
            <span class="material-symbols-outlined text-xs">chevron_right</span>
            <span class="font-semibold text-primary">Create</span>
        </div>
    </div>
    <div class="flex gap-3">
        <a href="/admin/resources/{{ $resource }}"
           class="px-5 py-2.5 rounded-xl text-sm font-semibold text-on-secondary-container bg-secondary-container hover:bg-slate-200 transition-all">
            Cancel
        </a>
    </div>
</div>

{{-- Form Grid --}}
<div class="grid grid-cols-12 gap-8 max-w-5xl">

    {{-- Main Form --}}
    <div class="col-span-12 lg:col-span-8">
        <form action="/admin/resources/{{ $resource }}" method="POST" enctype="multipart/form-data">

            <section class="bg-surface-container-lowest p-8 rounded-2xl shadow-[0_12px_32px_-4px_rgba(42,52,57,0.06)] space-y-6">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                    <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                    <h3 class="text-lg font-bold tracking-tight font-headline">{{ ucfirst($resource) }} Details</h3>
                </div>

                @if(isset($fields) && count($fields))

                    @foreach($fields as $field)
                        <?php echo app('blade')->render('admin::fields.' . ($field['type'] ?? 'text'), array_merge(get_defined_vars(), ['field' => $field, 'value' => $field['default'] ?? ''])); ?>
                    @endforeach

                @else
                    {{-- Fallback generic form --}}
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Name</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">badge</span>
                            <input type="text" id="name" name="name" required
                                   class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                          focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                          text-on-surface placeholder:text-on-surface-variant/50 transition-all"
                                   placeholder="Enter name…">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Email</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">mail</span>
                            <input type="email" id="email" name="email" required
                                   class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                          focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                          text-on-surface placeholder:text-on-surface-variant/50 transition-all"
                                   placeholder="Enter email…">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">lock</span>
                            <input type="password" id="password" name="password" required
                                   class="w-full bg-surface-container-high border-0 border-b-2 border-transparent
                                          focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                          text-on-surface placeholder:text-on-surface-variant/50 transition-all"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="role" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">Role</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">manage_accounts</span>
                            <select id="role" name="role"
                                    class="w-full appearance-none bg-surface-container-high border-0 border-b-2 border-transparent
                                           focus:border-primary focus:ring-0 rounded-t-xl pl-10 pr-4 py-3 text-sm
                                           text-on-surface transition-all cursor-pointer">
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="viewer">Viewer</option>
                            </select>
                        </div>
                    </div>
                @endif

                {{-- Submit row --}}
                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary to-primary-dim
                                   text-on-primary text-sm font-bold rounded-xl shadow-lg shadow-primary/20
                                   hover:opacity-90 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings:'FILL' 1;">save</span>
                        Create {{ ucfirst(rtrim($resource, 's')) }}
                    </button>
                    <a href="/admin/resources/{{ $resource }}"
                       class="flex items-center gap-2 px-5 py-2.5 bg-surface-container-highest text-on-surface-variant
                              text-sm font-semibold rounded-xl hover:bg-surface-variant transition-all">
                        Cancel
                    </a>
                </div>
            </section>

        </form>
    </div>

    {{-- Side Tips --}}
    <div class="col-span-12 lg:col-span-4">
        <div class="bg-slate-900 text-white p-8 rounded-2xl shadow-xl sticky top-24">
            <span class="material-symbols-outlined text-primary mb-4 text-3xl" style="font-variation-settings:'FILL' 1;">lightbulb</span>
            <h3 class="text-lg font-bold mb-3 font-headline">Quick Help</h3>
            <p class="text-slate-400 text-xs leading-relaxed mb-4">Fill in the required fields marked with <span class="text-error">*</span>. All data is validated server-side before saving.</p>
            <ul class="text-slate-400 text-xs leading-loose list-disc list-inside space-y-1">
                <li>Fields marked required cannot be left blank.</li>
                <li>Email must be a valid format.</li>
                <li>Password will be hashed automatically.</li>
            </ul>
        </div>
    </div>
</div>

@endsection
