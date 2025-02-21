<div class="js-cookie-consent cookie-consent {{ $pluginConfig['position'] === 'start' ? 'sticky left-0 top-0' : 'fixed bottom-0 left-0' }} z-50 w-full p-4 bg-white border-t border-gray-200 shadow md:p-6 dark:bg-gray-800 dark:border-gray-600">
    <div class="flex justify-center flex-row">
        <div class="basis-3/4 max-w-7xl">
            {!! trans('cookie-consent::texts.message') !!}<br>
            {!! trans('cookie-consent::texts.question') !!}
        </div>
        <div class="basis-1/4 mx-3 flex flex-col xl:flex-row">
            <x-filament::button
                size="{{ $pluginConfig['consent_button']['size'] ?? 'sm' }}"
                color="{{ $pluginConfig['consent_button']['color'] ?? 'warning' }}"
                class="js-cookie-consent-agree cookie-consent__agree w-full xl:w-auto"
            >
                {{ trans('cookie-consent::texts.agree') }}
            </x-filament::button>
            <x-filament::button
                size="{{ $pluginConfig['privacy_policy_button']['size'] ?? 'sm' }}"
                color="{{ $pluginConfig['privacy_policy_button']['color'] ?? 'gray' }}"
                class="js-cookie-consent-refuse cookie-consent__refuse w-full xl:w-auto"
            >
                {{ trans('cookie-consent::texts.refuse') }}
            </x-filament::button>
        </div>
    </div>
</div>
