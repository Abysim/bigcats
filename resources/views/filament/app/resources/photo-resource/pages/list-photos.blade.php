<x-filament-panels::page>
    <div x-data="{ extraPhotos: [] }"
         @photos-loaded.window="extraPhotos.push(...$event.detail.photos)">
        <div class="photo-gallery" wire:ignore>
            @foreach($this->initialPhotos as $photo)
                <a href="{{ $photo['flickr_link'] }}"
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

            <template x-for="photo in extraPhotos" :key="photo.id">
                <a :href="photo.flickr_link"
                   target="_blank"
                   rel="noopener noreferrer"
                   :title="`${photo.name} 📷 ${photo.author_name}`"
                   class="photo-item"
                   :style="`aspect-ratio: ${photo.thumbnail_width}/${photo.thumbnail_height};`">
                    <img :src="photo.thumbnail_url"
                         :width="photo.thumbnail_width"
                         :height="photo.thumbnail_height"
                         :alt="photo.name"
                         loading="lazy">
                </a>
            </template>
        </div>

        @if($this->hasMore)
            <div
                wire:key="sentinel-{{ $cursorId }}"
                x-intersect:enter="$wire.loadMore()"
                class="flex justify-center py-8"
            >
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        @endif
    </div>
</x-filament-panels::page>
