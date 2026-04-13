@php
    $layout = $page::getLayout() ?? 'admin::layouts.admin';
    $title = $page::getNavigationLabel();
    $icon = $page::getIcon();
@endphp

@extends($layout)

@section('content')
    <div class="mb-8">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-on-surface/5 rounded-lg text-on-surface-variant flex items-center justify-center">
                <span class="material-symbols-outlined text-xl">{{ $icon }}</span>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-on-surface font-headline">{{ $title }}</h1>
        </div>
    </div>
    
    <div>
        {!! app('blade')->render($view, $data) !!}
    </div>
@endsection
