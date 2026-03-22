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

        <x-article-children-grid :children="$this->record->publishedChildren" />
    </x-filament::section>
</x-filament-panels::page>
