{{--
    TextColumn cell.

    Expects $column (a column's viewData array) and $item (the record).
    Renders its own <td> so the resource table can include any column partial
    in the same place without knowing which one it got.
--}}
@php
    $value = \Libxa\Admin\Columns\AdminColumn::valueFor($column, $item);

    $limit = $column['limit'] ?? 0;
    $display = $value === null || $value === '' ? '—' : (string) $value;

    if ($limit > 0 && mb_strlen($display) > $limit) {
        $display = mb_substr($display, 0, $limit) . '…';
    }
@endphp

<td class="px-6 py-4 text-sm text-on-surface-variant">
    <div class="flex items-center gap-2 group/cell">
        <span class="{{ ($column['wrap'] ?? false) ? 'whitespace-normal break-words' : 'whitespace-nowrap' }}">
            {{-- html() is opt-in per column, so escaping stays the default. --}}
            @if($column['isHtml'] ?? false)
                {!! $display !!}
            @else
                {{ $display }}
            @endif
        </span>

        @if(($column['copyable'] ?? false) && $value !== null)
            <button type="button"
                    onclick="navigator.clipboard.writeText({{ json_encode((string) $value) }})"
                    class="opacity-0 group-hover/cell:opacity-100 transition-opacity p-0.5 rounded hover:text-primary text-on-surface-variant"
                    title="Copy value">
                <span class="material-symbols-outlined text-xs">content_copy</span>
            </button>
        @endif
    </div>
</td>
