<x-filament-widgets::widget>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        @if ($this->getHeading())
            <div class="fi-ta-header flex flex-col gap-3 p-4 sm:px-6 sm:flex-row sm:items-center border-b border-gray-200 dark:border-gray-700">
                <div class="grid gap-y-1">
                    <h3 class="fi-ta-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        {{ $this->getHeading() }}
                    </h3>
                </div>
            </div>
        @endif

        <div class="flex flex-wrap gap-3 p-4 sm:px-6 justify-center items-center">
            @foreach($this->getTags() as $tag)
                <a rel="tag" href="{{ $tag['url'] }}" style="font-size: {{ $tag['weight'] }}px">
                    {{ $tag['name'] }}
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
