<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @foreach($stats as $stat)
        @php
            $statData = $stat->toArray();
            $color = $statData['color'] ?: 'primary';
            $descColor = $statData['descriptionColor'] ?: 'slate';
            $icon = $statData['icon'] ?: 'analytics';
        @endphp
        
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-sm border border-transparent hover:border-primary/5 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-{{ $color }}-container text-{{ $color }} rounded-xl group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined">{{ $icon }}</span>
                </div>
                @if($statData['description'])
                    <span class="text-xs font-bold text-{{ $descColor }}-600 bg-{{ $descColor }}-50 px-2 py-1 rounded-full flex items-center gap-1">
                        @if($statData['descriptionIcon'])
                            <span class="material-symbols-outlined text-xs">{{ $statData['descriptionIcon'] }}</span>
                        @endif
                        {{ $statData['description'] }}
                    </span>
                @endif
            </div>
            <p class="text-on-surface-variant text-sm font-semibold font-headline">{{ $statData['label'] }}</p>
            <p class="text-2xl font-extrabold text-on-surface mt-1">{{ $statData['value'] }}</p>
        </div>
    @endforeach
</div>
