<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Games</title>
<style>body{background:#12040d;color:#fff;font-family:sans-serif;margin:0;padding:16px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
.c{background:#2a0a1d;border-radius:10px;overflow:hidden;text-decoration:none;color:#fff}
.c img{width:100%;height:110px;object-fit:cover;background:#400}.c div{padding:8px;font-size:13px}
.e{background:#7a1020;padding:10px;border-radius:8px;margin-bottom:12px}</style></head><body>
<h2>Games</h2>
@if(session('error'))<div class="e">{{ session('error') }}</div>@endif
@if($error)<div class="e">{{ $error }}</div>@endif
<div class="grid">
@foreach($games as $g)
<a class="c" target="_blank" rel="noopener" href="{{ route('astech.launch', $g['gameId']) }}">
<img src="{{ $g['imageUrl'] ?? '' }}" alt="" loading="lazy">
<div><b>{{ $g['title'] ?? '' }}</b><br><small>{{ $g['provider'] ?? '' }}</small></div></a>
@endforeach
</div></body></html>
