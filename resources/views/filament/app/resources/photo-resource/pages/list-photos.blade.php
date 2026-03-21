<x-filament-panels::page>
    @assets
    @vite('resources/js/photo-gallery.js')
    @endassets

    <div>
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
        </div>

        @if($this->hasMore)
            <div
                wire:key="sentinel-{{ $cursorId }}"
                x-data
                x-intersect:enter="$wire.loadMore()"
                class="flex justify-center py-8"
            >
                <x-filament::loading-indicator class="h-8 w-8" />
            </div>
        @endif
    </div>

    @script
    <script>
        const MasonryInfiniteGrid = window.MasonryInfiniteGrid;
        if (!MasonryInfiniteGrid) {
            console.error('[photo-gallery] MasonryInfiniteGrid not loaded');
            return;
        }

        const gallery = $wire.$el.querySelector('.photo-gallery');
        if (!gallery) return;

        // Responsive column count matching CSS breakpoints (rem-based)
        const breakpoints = [
            [window.matchMedia('(min-width: 80rem)'), 4],
            [window.matchMedia('(min-width: 64rem)'), 3],
            [window.matchMedia('(min-width: 40rem)'), 2],
        ];

        function getColumnCount() {
            for (const [mq, cols] of breakpoints) {
                if (mq.matches) return cols;
            }
            return 1;
        }

        const ig = new MasonryInfiniteGrid(gallery, {
            gap: 12,
            column: getColumnCount(),
            useRecycle: false,
            useResizeObserver: true,
            useTransform: false,
            align: 'stretch',
        });

        ig.renderItems();

        // Update column count on breakpoint change
        breakpoints.forEach(([mq]) => {
            mq.addEventListener('change', () => {
                if (!gallery.isConnected) return;
                ig.setOptions({ column: getColumnCount() });
                ig.renderItems();
            });
        });

        // Create photo element using DOM APIs (XSS-safe, no innerHTML round-trip)
        function createPhotoElement(photo) {
            const a = document.createElement('a');
            a.href = photo.flickr_link;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            a.title = photo.name + ' \u{1F4F7} ' + photo.author_name;
            a.className = 'photo-item';
            a.style.aspectRatio = photo.thumbnail_width + '/' + photo.thumbnail_height;

            const img = document.createElement('img');
            img.src = photo.thumbnail_url;
            img.width = photo.thumbnail_width;
            img.height = photo.thumbnail_height;
            img.alt = photo.name;
            img.loading = 'lazy';

            a.appendChild(img);
            return a;
        }

        // Listen for Livewire photo batches (component-scoped, auto-cleanup)
        let batchCount = 0;
        $wire.on('photos-loaded', ({ photos }) => {
            batchCount++;
            const elements = photos.map(createPhotoElement);
            ig.append(elements, batchCount);
        });
    </script>
    @endscript
</x-filament-panels::page>
