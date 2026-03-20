<x-filament-panels::page>
    <div class="photo-gallery">
        @foreach($photos as $photo)
            <a wire:key="photo-{{ $photo['id'] }}"
               href="{{ $photo['flickr_link'] }}"
               target="_blank"
               rel="noopener noreferrer"
               title="{{ $photo['name'] }} &#128247; {{ $photo['author_name'] }}"
               class="photo-item"
               style="aspect-ratio: {{ $photo['thumbnail_width'] }}/{{ $photo['thumbnail_height'] }};">
                <img src="{{ $photo['thumbnail_url'] }}"
                     width="{{ $photo['thumbnail_width'] }}"
                     height="{{ $photo['thumbnail_height'] }}"
                     alt="{{ $photo['name'] }}"
                     loading="lazy">
            </a>
        @endforeach
    </div>

    @if($hasMore)
        <div
            wire:key="sentinel-{{ count($photos) }}"
            x-data=""
            x-intersect:enter="$wire.loadMore()"
            class="flex justify-center py-8"
        >
            <x-filament::loading-indicator class="h-8 w-8" />
        </div>
    @endif
</x-filament-panels::page>
