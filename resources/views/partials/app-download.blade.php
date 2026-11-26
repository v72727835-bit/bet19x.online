@php
    $apkReady = \App\Http\Controllers\AppDownloadController::exists();
    $apkSize = $apkReady ? \App\Http\Controllers\AppDownloadController::sizeLabel() : null;
@endphp
{{-- Hidden until the script confirms the visitor is outside the app and not already installed --}}
<section class="bx-section" id="bx-appcard" hidden>
    <div class="bx-appcard">
        <img class="bx-appcard-icon" src="/icons/icon-192.png" alt="" width="64" height="64">
        <div class="bx-grow">
            <h2 class="bx-h2" style="font-size:24px;">Get the {{ $siteSetting->site_name ?? 'BET19X' }} app</h2>
            <p class="bx-sub" style="margin:4px 0 0;">Opens full screen, one tap from your home screen. Free.</p>
        </div>
        <div class="bx-appcard-actions">
            @if ($apkReady)
                <a href="{{ route('app.download') }}" class="bx-btn bx-btn-gold" id="bx-apk-btn" hidden download>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14"/></svg>
                    Download APK <span style="font-weight:700; opacity:.7;">{{ $apkSize }}</span>
                </a>
            @endif
            <button type="button" class="bx-btn bx-btn-ghost" id="bx-install-btn" hidden>Install app</button>
        </div>
        @if ($apkReady)
        <details class="bx-appcard-help" id="bx-apk-help" hidden>
            <summary>How to install the APK</summary>
            <ol>
                <li>Tap <b>Download APK</b> and open the file when it finishes.</li>
                <li>If Android asks, allow <b>Install from this source</b> for your browser, then tap <b>Install</b>.</li>
                <li>Open <b>BET19X</b> from your app list.</li>
            </ol>
        </details>
        @endif
        <details class="bx-appcard-help" id="bx-ios-help" hidden>
            <summary>How to add to your iPhone</summary>
            <ol>
                <li>Open this page in <b>Safari</b>.</li>
                <li>Tap the <b>Share</b> button, then <b>Add to Home Screen</b>.</li>
            </ol>
        </details>
    </div>
</section>

<script>
(function () {
    var card = document.getElementById('bx-appcard');
    if (!card) return;
    var ua = navigator.userAgent || '';
    var inApp = /BET19XApp\//.test(ua);
    var standalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
    if (inApp || standalone) return;                       // already using the app

    var isIOS = /iPhone|iPad|iPod/.test(ua);
    var isAndroid = /Android/i.test(ua);
    var apk = document.getElementById('bx-apk-btn');
    var install = document.getElementById('bx-install-btn');

    function show() { card.hidden = false; }

    if (isIOS) {
        document.getElementById('bx-ios-help').hidden = false;      // iOS cannot install an APK
        show();
    } else if (apk) {
        apk.hidden = false;
        var help = document.getElementById('bx-apk-help');
        if (isAndroid && help) help.hidden = false;
        show();
    }

    function offerInstall() {
        if (window.bxInstallEvent) { install.hidden = false; show(); }
    }
    install.addEventListener('click', function () {
        var ev = window.bxInstallEvent;
        if (!ev) return;
        ev.prompt();
        ev.userChoice.finally(function () { window.bxInstallEvent = null; install.hidden = true; });
    });
    offerInstall();
    window.addEventListener('bx-install-ready', offerInstall);
    window.addEventListener('bx-installed', function () { card.hidden = true; });
})();
</script>
