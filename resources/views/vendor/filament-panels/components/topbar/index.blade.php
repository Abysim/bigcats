{{-- Forked from filament/filament v3.3.49 — adds CSS-hover dropdown for labeled nav groups --}}
@props([
    'navigation',
])

<div
    {{
        $attributes->class([
            'fi-topbar sticky top-0 z-20 overflow-x-clip',
            'fi-topbar-with-navigation' => filament()->hasTopNavigation(),
        ])
    }}
>
    <nav
        class="flex h-16 items-center gap-x-4 bg-white px-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 md:px-6 lg:px-8"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START) }}

        @if (filament()->hasNavigation())
            <x-filament::icon-button
                color="gray"
                icon="heroicon-o-bars-3"
                icon-alias="panels::topbar.open-sidebar-button"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.open()"
                x-show="! $store.sidebar.isOpen"
                @class([
                    'fi-topbar-open-sidebar-btn',
                    'lg:hidden' => (! filament()->isSidebarFullyCollapsibleOnDesktop()) || filament()->isSidebarCollapsibleOnDesktop(),
                ])
            />

            <x-filament::icon-button
                color="gray"
                icon="heroicon-o-x-mark"
                icon-alias="panels::topbar.close-sidebar-button"
                icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                x-cloak
                x-data="{}"
                x-on:click="$store.sidebar.close()"
                x-show="$store.sidebar.isOpen"
                class="fi-topbar-close-sidebar-btn lg:hidden"
            />
        @endif

        @if (filament()->hasTopNavigation() || (! filament()->hasNavigation()))
            <div class="me-6 hidden lg:flex">
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <x-filament-panels::logo />
                    </a>
                @else
                    <x-filament-panels::logo />
                @endif
            </div>

            @if (filament()->hasTenancy() && filament()->hasTenantMenu())
                <x-filament-panels::tenant-menu class="hidden lg:block" />
            @endif

            @if (filament()->hasNavigation())
                <ul class="me-4 hidden items-center gap-x-4 lg:flex">
                    @foreach ($navigation as $group)
                        @if ($groupLabel = $group->getLabel())
                            <li class="fi-topbar-dropdown relative">
                                <a href="{{ filament()->getHomeUrl() ?? url('/') }}"
                                   @class([
                                       'fi-topbar-item flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm font-medium transition',
                                       'text-primary-600 bg-primary-50 dark:text-primary-400 dark:bg-primary-950' => $group->isActive(),
                                       'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' => ! $group->isActive(),
                                   ])>
                                    @svg('heroicon-o-home', 'h-5 w-5')
                                    {{ $groupLabel }}
                                    @svg('heroicon-m-chevron-down', 'h-4 w-4 opacity-50')
                                </a>

                                <div class="fi-topbar-dropdown-panel hidden absolute left-0 top-full z-30 mt-1 min-w-[28rem] rounded-xl bg-white shadow-lg border border-gray-950/5 dark:bg-gray-900 dark:border-white/10">
                                    <div class="grid grid-cols-2 gap-1 p-2">
                                        @foreach ($group->getItems() as $item)
                                            @php
                                                $itemIsActive = $item->isActive();
                                            @endphp
                                            <a href="{{ $item->getUrl() }}"
                                               @if ($item->shouldOpenUrlInNewTab()) target="_blank" @endif
                                               @class([
                                                   'flex items-center gap-x-2 rounded-lg px-3 py-2 text-sm transition',
                                                   'text-primary-600 bg-primary-50 dark:text-primary-400 dark:bg-primary-950' => $itemIsActive,
                                                   'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' => ! $itemIsActive,
                                               ])>
                                                {{ $item->getLabel() }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        @else
                            @foreach ($group->getItems() as $item)
                                <x-filament-panels::topbar.item
                                    :active="$item->isActive()"
                                    :active-icon="$item->getActiveIcon()"
                                    :badge="$item->getBadge()"
                                    :badge-color="$item->getBadgeColor()"
                                    :badge-tooltip="$item->getBadgeTooltip()"
                                    :icon="$item->getIcon()"
                                    :should-open-url-in-new-tab="$item->shouldOpenUrlInNewTab()"
                                    :url="$item->getUrl()"
                                >
                                    {{ $item->getLabel() }}
                                </x-filament-panels::topbar.item>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            @endif
        @endif

        <div
            @if (filament()->hasTenancy())
                x-persist="topbar.end.panel-{{ filament()->getId() }}.tenant-{{ filament()->getTenant()?->getKey() }}"
            @else
                x-persist="topbar.end.panel-{{ filament()->getId() }}"
            @endif
            class="ms-auto flex items-center gap-x-4"
        >
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled())
                @livewire(Filament\Livewire\GlobalSearch::class)
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    @livewire(Filament\Livewire\DatabaseNotifications::class, [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                <x-filament-panels::user-menu />
            @endif
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END) }}
    </nav>
</div>
