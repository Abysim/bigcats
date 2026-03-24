<x-filament-panels::page>
    <x-filament::section>
        @if($this->record->image)
            @php $caption = $this->record->image_caption; @endphp
            <img src="{{ Storage::url($this->record->image) }}"
                 alt="{{ $caption ?? $this->record->title }}"
                 @class(['w-full rounded-lg', 'mb-1' => $caption, 'mb-6' => !$caption])>
            @if($caption)
                <p class="text-sm text-gray-500 dark:text-gray-400 italic mb-6">{{ $caption }}</p>
            @endif
        @endif

        @if($this->record->content)
            <div class="prose dark:prose-invert max-w-none">
                {!! $this->record->content !!}
            </div>
        @endif

        <x-article-children-grid :children="$this->record->publishedChildren" />
    </x-filament::section>
</x-filament-panels::page>
