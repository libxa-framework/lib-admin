{{--
    BadgeColumn cell.

    The colour map is keyed by the *formatted* value, so a column that turns a
    timestamp into "subscribed"/"unsubscribed" can colour those words rather
    than the raw column value, which nothing would ever match.
--}}
@php
    $value = \Libxa\Admin\Columns\AdminColumn::valueFor($column, $item);

    $key = $value === null ? '' : (string) $value;
    $colors = $column['colors'] ?? [];

    // Tailwind builds its stylesheet by scanning source, so an interpolated
    // class name is only safe when the palette is one it has already seen.
    // Keeping the fallback to a real colour avoids an unstyled pill.
    $color = $colors[$key] ?? 'slate';
@endphp

<td class="px-6 py-4 text-sm">
    @if($key === '')
        <span class="text-on-surface-variant">—</span>
    @else
        <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                    bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200 shadow-sm shadow-{{ $color }}-100/50">
            {{ ucfirst($key) }}
        </div>
    @endif
</td>
