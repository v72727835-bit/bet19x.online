@php
    $heroBanners = $heroBanners ?? \App\Models\HeroBanner::where('is_active', true)->orderBy('sort_order')->get();
@endphp

@if ($heroBanners->count() > 0)
<style>
    @keyframes fxHeroScroll {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .fx-hero-track {
        animation: fxHeroScroll 30s linear infinite;
        width: max-content;
    }
    .fx-hero-track:hover {
        animation-play-state: paused;
    }
</style>
<div style="background-color:#0F1023;" class="overflow-hidden py-6">
    <div class="flex">
        <div class="flex gap-4 fx-hero-track px-2">
            @foreach ($heroBanners as $banner)
                @php $img = str_starts_with($banner->image_path, 'http') ? $banner->image_path : asset('storage/' . $banner->image_path); @endphp
                @if ($banner->link_url)
                    <a href="{{ $banner->link_url }}" class="shrink-0 block rounded-xl overflow-hidden shadow-lg" style="width:340px; height:180px; border:1px solid #24254A;">
                        <img src="{{ $img }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                    </a>
                @else
                    <div class="shrink-0 block rounded-xl overflow-hidden shadow-lg" style="width:340px; height:180px; border:1px solid #24254A;">
                        <img src="{{ $img }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                    </div>
                @endif
            @endforeach
            @foreach ($heroBanners as $banner)
                @php $img = str_starts_with($banner->image_path, 'http') ? $banner->image_path : asset('storage/' . $banner->image_path); @endphp
                <div class="shrink-0 block rounded-xl overflow-hidden shadow-lg" style="width:340px; height:180px; border:1px solid #24254A;" aria-hidden="true">
                    <img src="{{ $img }}" alt="{{ $banner->title ?? 'Banner' }}" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
