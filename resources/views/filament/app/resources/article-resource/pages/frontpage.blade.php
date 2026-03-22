<x-filament-panels::page>
    @if($article)
        <x-filament::section>
            @if($article->content)
                <div class="prose dark:prose-invert max-w-none">
                    {!! $article->content !!}
                </div>
            @endif

            <x-article-children-grid :children="$article->featuredChildren" />
        </x-filament::section>
    @endif
</x-filament-panels::page>
