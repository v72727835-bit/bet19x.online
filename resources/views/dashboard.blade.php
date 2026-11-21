<x-app-layout>
    <div class="bx-wrap">
        @if ($heroBanners->count())
            <div class="bx-hero" id="bx-hero">
                @foreach ($heroBanners as $i => $banner)
                    @php
                        $img = str_starts_with($banner->image_path, 'http') ? $banner->image_path : asset('storage/' . $banner->image_path);
                    @endphp
                    <a href="{{ $banner->link_url ?: '#games' }}" class="{{ $i === 0 ? 'is-on' : '' }}">
                        <img src="{{ $img }}" alt="Offer {{ $i + 1 }}" @if($i > 0) loading="lazy" @endif>
                    </a>
                @endforeach
                @if ($heroBanners->count() > 1)
                    <div class="bx-hero-dots">
                        @foreach ($heroBanners as $i => $banner)
                            <button type="button" class="{{ $i === 0 ? 'is-on' : '' }}" aria-label="Show offer {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($heroBanners->count() > 1)
                <script>
                (function () {
                    var root = document.getElementById('bx-hero');
                    var slides = root.querySelectorAll('a'), dots = root.querySelectorAll('.bx-hero-dots button'), i = 0, t;
                    function go(n) {
                        slides[i].classList.remove('is-on'); dots[i].classList.remove('is-on');
                        i = (n + slides.length) % slides.length;
                        slides[i].classList.add('is-on'); dots[i].classList.add('is-on');
                    }
                    function auto() { clearInterval(t); t = setInterval(function () { go(i + 1); }, 5000); }
                    dots.forEach(function (d, n) { d.addEventListener('click', function () { go(n); auto(); }); });
                    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) auto();
                })();
                </script>
            @endif
        @endif

        @include('partials.wallet-bar')

        @include('partials.astech-games')

        @if (isset($topWinners) && $topWinners->count())
            <section class="bx-section">
                <div class="bx-section-head"><h2 class="bx-h2">Recent big wins</h2></div>
                <div class="bx-panel" style="padding:4px 16px;">
                    <ul class="bx-list">
                        @foreach ($topWinners as $winner)
                            <li>
                                @if ($winner->avatar)
                                    <img class="bx-avatar" src="{{ $winner->avatar }}" alt="">
                                @else
                                    <span class="bx-avatar" style="display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--bx-gold);">{{ strtoupper(substr($winner->name, 0, 1)) }}</span>
                                @endif
                                <div class="bx-grow">
                                    <b>{{ $winner->name }}</b>
                                    <small>{{ $winner->contest_name }}</small>
                                </div>
                                <span class="bx-money bx-plus" style="font-size:20px;">&#8377;{{ number_format($winner->amount_won) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if (isset($siteStats) && $siteStats->count())
            <section class="bx-section">
                <div class="bx-stats">
                    @foreach ($siteStats as $stat)
                        <div class="bx-stat">
                            <b>{{ $stat->value }}</b>
                            <span>{{ $stat->label }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <footer class="bx-footer">
            <div class="bx-18">18+</div>
            <p>{{ $siteSetting->footer_text ?? 'Play for fun, within your limits. Only for players aged 18 and over.' }}</p>
            <p>Pay with UPI, Paytm, net banking or card.</p>
            <p>&copy; {{ date('Y') }} {{ $siteSetting->site_name ?? 'BET19X' }}</p>
        </footer>
    </div>
</x-app-layout>
