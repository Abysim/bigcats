<x-filament-panels::page>
    <x-filament::section>
        @if($this->record->image)
            <img src="{{ Storage::url($this->record->image) }}"
                 alt="{{ $this->record->image_caption }}"
                 class="w-full rounded-lg mb-6">
        @endif

        @if($this->record->content)
            <div class="prose dark:prose-invert max-w-none">
                {!! $this->record->content !!}
            </div>
        @endif

        @if($this->record->publishedChildren->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 not-prose">
                @foreach($this->record->publishedChildren as $child)
                    <a href="{{ $child->getUrl() }}" class="block group">
                        @if($child->image)
                            <img src="{{ Storage::url($child->image) }}"
                                 alt="{{ $child->title }}"
                                 class="w-full aspect-[4/3] object-cover rounded-lg">
                        @endif
                        <h3 class="mt-2 text-sm font-semibold group-hover:text-primary-600 dark:group-hover:text-primary-400">
                            {{ $child->title }}
                        </h3>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
