{{--
    TextColumn cell partial — renders a single cell value in a resource table.
    Expected: $column (viewData array), $item (Eloquent model)
--}}
@php
    $name     = $column['name'];
    $copyable = $column['copyable'] ?? false;
    $wrap     = $column['wrap'] ?? false;
    $limit    = $column['limit'] ?? 0;

    $raw = $item->$name ?? null;
    $val = $raw ?? '—';

    if ($limit > 0 && is_string($val) && mb_strlen($val) > $limit) {
        $val = mb_substr($val, 0, $limit) . '…';
    }
@endphp

<td class="px-6 py-4 text-sm text-on-surface-variant">
    <div class="flex items-center gap-2 group/cell">
        <span class="{{ $wrap ? 'whitespace-normal break-words' : 'whitespace-nowrap' }}">{{ $val }}</span>

        @if($copyable && $raw !== null)
            <button type="button"
                    onclick="navigator.clipboard.writeText({{ json_encode((string) $raw) }})"
                    class="opacity-0 group-hover/cell:opacity-100 transition-opacity p-0.5 rounded hover:text-primary text-on-surface-variant"
                    title="Copy value">
                <span class="material-symbols-outlined text-xs">content_copy</span>
            </button>
        @endif
    </div>
</td>
