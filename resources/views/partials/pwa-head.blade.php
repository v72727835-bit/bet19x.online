{{-- Progressive web app: manifest, icons, iOS meta, service worker --}}
<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ $siteSetting->site_name ?? 'BET19X' }}">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<script>
(function () {
    // Remember the browser's install prompt so any "Install app" button can use it.
    window.bxInstallEvent = null;
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.bxInstallEvent = e;
        window.dispatchEvent(new CustomEvent('bx-install-ready'));
    });
    window.addEventListener('appinstalled', function () {
        window.bxInstallEvent = null;
        window.dispatchEvent(new CustomEvent('bx-installed'));
    });
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
        });
    }
})();
</script>
