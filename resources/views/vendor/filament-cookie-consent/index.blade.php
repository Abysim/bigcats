@if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies)

    @include('filament-cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_VALUE = 1;
            const REJECT_VALUE = 0;
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            function refuseWithCookies() {
                setCookie('{{ $cookieConsentConfig['cookie_name'] }}', REJECT_VALUE, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
            }

            function cookieExists(name) {
                return (document.cookie.split('; ').indexOf(name + '=' + COOKIE_VALUE) !== -1);
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (cookieExists('{{ $cookieConsentConfig['cookie_name'] }}')) {
                hideCookieDialog();
            }

            const refuseButtons = document.getElementsByClassName('js-cookie-consent-refuse');

            for (let i = 0; i < refuseButtons.length; ++i) {
                refuseButtons[i].addEventListener('click', refuseWithCookies);
            }

            return {
                refuseWithCookies: refuseWithCookies,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
