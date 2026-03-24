@props(['children'])

@if($children->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 not-prose">
        @foreach($children as $child)
            <a href="{{ $child->getUrl() }}"
               class="group block rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow duration-200">
                @if($child->image)
                    <div class="overflow-hidden">
                        <img src="{{ Storage::url($child->image) }}"
                             alt="{{ $child->title }}"
                             loading="lazy"
                             class="w-full aspect-[4/3] object-cover transition-transform duration-300 group-hover:scale-105">
                    </div>
                @endif
                <div class="p-3">
                    <h3 class="text-base font-semibold leading-snug text-gray-900 dark:text-gray-100 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-150">
                        {{ $child->title }}
                    </h3>
                    @if($child->resume)
                        <p class="prose dark:prose-invert max-w-none mt-1 text-sm leading-relaxed line-clamp-3">
                            {{ $child->resume }}
                        </p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
@endif
