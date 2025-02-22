<footer class="mx-auto h-full w-full px-4 md:px-6 lg:px-8 max-w-screen-2xl pb-6">
    <div class="text-center md:text-right px-1">
        <div class="inline-flex gap-3">
            <a href="https://t.me/bigcats_ua" class="text-gray-700 dark:text-primary-400" title="Телеграм" target="_blank" rel="me">
                <x-fab-telegram-plane class="w-12 h-12" alt="Іконка Телеграм" />
            </a>
            <a href="https://instagram.com/bigcats_ua" class="text-gray-700 dark:text-primary-400" title="Інстаграм" target="_blank" rel="me">
                <x-fab-instagram class="w-12 h-12" alt="Іконка Інстаграм" />
            </a>
            <a href="https://bsky.app/profile/bigcats.org.ua" class="text-gray-700 dark:text-primary-400" title="Блюскай" target="_blank" rel="me">
                <x-fab-bluesky class="w-12 h-12" alt="Іконка Блюскай" />
            </a>
            <a href="https://threads.net/@bigcats_ua" class="text-gray-700 dark:text-primary-400" title="Тредс" target="_blank" rel="me">
                <x-fab-threads class="w-12 h-12" alt="Іконка Тредс" />
            </a>
            <a href="https://twitter.com/bigcats_ua" class="text-gray-700 dark:text-primary-400" title="Твіттер" target="_blank" rel="me">
                <x-fab-twitter class="w-12 h-12" alt="Іконка Твіттер" />
            </a>
            <a href="https://facebook.com/bigcats.ua" class="text-gray-700 dark:text-primary-400" title="Фейсбук" target="_blank" rel="me">
                <x-fab-facebook-f class="w-11 h-11" alt="Іконка Фейсбук" />
            </a>
        </div>
    </div>
</footer>

<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}

    gtag('consent', 'default', {
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'analytics_storage': 'denied'
    });
</script>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-2KC7QMPCBG"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-2KC7QMPCBG');
</script>
<!-- End Google tag (gtag.js) -->
<script>
    function consentGranted() {
        gtag('consent', 'update', {
            'ad_storage': 'granted',
            'ad_user_data': 'granted',
            'ad_personalization': 'granted',
            'analytics_storage': 'granted'
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        if (document.cookie.includes('laravel_cookie_consent=1')) {
            consentGranted();
        }
    });
</script>
