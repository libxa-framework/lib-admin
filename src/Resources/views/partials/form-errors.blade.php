{{--
    Form errors flashed by ResourceController.

    Its store()/update() flash under 'errors' as ['field' => [messages]], and
    nothing rendered them — a save that failed redirected back to a form that
    looked exactly as it had before, so the record silently did not save.
--}}
@php
    $formErrors = session('errors') ?? [];
@endphp

@if(is_array($formErrors) && $formErrors !== [])
    <div class="flex items-start gap-3 p-4 mb-6 bg-red-50 border border-red-200 rounded-xl text-sm">
        <span class="material-symbols-outlined text-error mt-0.5 shrink-0">error</span>
        <div class="space-y-1">
            @foreach($formErrors as $messages)
                @foreach((array) $messages as $message)
                    <p class="text-on-error-container font-medium">{{ $message }}</p>
                @endforeach
            @endforeach
        </div>
    </div>
@endif
