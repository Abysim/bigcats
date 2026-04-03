<x-filament-panels::page>
    @if($article)
        <x-filament::section>
            @if($article->content)
                <div class="prose dark:prose-invert max-w-none">
                    {!! $article->content !!}
                </div>
            @endif

            {{ $this->childrenInfolist }}
        </x-filament::section>
    @endif
</x-filament-panels::page>
