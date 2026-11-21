<section id="ag-root" style="max-width:1200px;margin:32px auto 90px;padding:0 16px;font-family:'Rajdhani',sans-serif;color:#fff">
<style>
#ag-root h3{font-size:22px;font-weight:800;text-align:center;margin:0 0 14px}
#ag-root .ag-bar{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px}
#ag-root .ag-bar input,#ag-root .ag-bar select{flex:1;min-width:140px;background:#1b1f45;color:#fff;border:1px solid #3a3f78;border-radius:10px;padding:10px 12px;font-size:15px}
#ag-root .ag-chips{display:flex;gap:8px;overflow-x:auto;padding-bottom:8px;margin-bottom:10px}
#ag-root .ag-chip{white-space:nowrap;background:#1b1f45;border:1px solid #3a3f78;color:#fff;border-radius:999px;padding:6px 14px;cursor:pointer;font-size:14px}
#ag-root .ag-chip.on{background:linear-gradient(135deg,#ff4d8d,#ff2d6f);border-color:transparent}
#ag-root .ag-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px}
#ag-root .ag-card{display:block;background:#1b1f45;border:1px solid #2c3166;border-radius:12px;overflow:hidden;text-decoration:none;color:#fff;transition:transform .15s}
#ag-root .ag-card:hover{transform:translateY(-3px);border-color:#ff4d8d}
#ag-root .ag-img{aspect-ratio:4/3;background:linear-gradient(135deg,#2a2f66,#ff2d6f55);display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:800}
#ag-root .ag-img img{width:100%;height:100%;object-fit:cover}
#ag-root .ag-t{padding:8px 10px}#ag-root .ag-t b{display:block;font-size:14px;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
#ag-root .ag-t small{color:#9aa0d8;font-size:12px}
#ag-root .ag-more{display:block;margin:16px auto 0;padding:10px 28px;border:0;border-radius:10px;background:linear-gradient(135deg,#ff4d8d,#ff2d6f);color:#fff;font-weight:700;font-size:15px;cursor:pointer}
#ag-root .ag-msg{text-align:center;padding:14px;border-radius:10px;background:#7a1020;margin-bottom:12px}
</style>
<h3> Casino &amp; Games</h3>
@if(session('error'))<div class="ag-msg">{{ session('error') }}</div>@endif
<div id="ag-err" class="ag-msg" style="display:none"></div>
<div class="ag-bar"><input id="ag-q" type="search" placeholder="Search games..."><select id="ag-c"><option value="">All categories</option></select><select id="ag-p"><option value="">All providers</option></select></div>
<div class="ag-grid" id="ag-grid"></div>
<button class="ag-more" id="ag-more" style="display:none">Load more</button>
<script>
(function(){
 var L="{{ route('astech.list') }}",M="{{ route('astech.meta') }}",LA="{{ url('/games/launch') }}/";
 var st={q:'',category:'',provider:'',page:1},g=document.getElementById('ag-grid'),more=document.getElementById('ag-more'),err=document.getElementById('ag-err'),busy=false,tm;
 function card(x){var a=document.createElement('a');a.className='ag-card';a.href=LA+encodeURIComponent(x.id);
  var i=document.createElement('div');i.className='ag-img';
  if(x.img){var im=document.createElement('img');im.loading='lazy';im.alt='';im.src=x.img;im.onerror=function(){i.textContent=(x.title||'?').charAt(0)};i.appendChild(im)}else{i.textContent=(x.title||'?').charAt(0)}
  var t=document.createElement('div');t.className='ag-t';var b=document.createElement('b');b.textContent=x.title;var s=document.createElement('small');s.textContent=x.provider;t.appendChild(b);t.appendChild(s);a.appendChild(i);a.appendChild(t);return a}
 function load(reset){if(busy)return;busy=true;if(reset){st.page=1;g.innerHTML=''}
  var u=L+'?q='+encodeURIComponent(st.q)+'&category='+encodeURIComponent(st.category)+'&provider='+encodeURIComponent(st.provider)+'&page='+st.page;
  fetch(u,{headers:{'Accept':'application/json'},credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){
   if(d.error){err.textContent='Games load nahi hue: '+d.error;err.style.display='block'}else{err.style.display='none'}
   (d.data||[]).forEach(function(x){g.appendChild(card(x))});
   more.style.display=(st.page*d.per<d.total)?'block':'none';
   if(!d.total&&!d.error){g.innerHTML='<div style="grid-column:1/-1;text-align:center;color:#9aa0d8">Koi game nahi mila</div>'}
  }).catch(function(){err.textContent='Games load nahi hue, dobara try karein.';err.style.display='block'}).finally(function(){busy=false})}
 more.onclick=function(){st.page++;load(false)};
 document.getElementById('ag-q').oninput=function(e){clearTimeout(tm);var v=e.target.value;tm=setTimeout(function(){st.q=v;load(true)},300)};
 document.getElementById('ag-c').onchange=function(e){st.category=e.target.value;load(true)};
 document.getElementById('ag-p').onchange=function(e){st.provider=e.target.value;load(true)};
 fetch(M,{headers:{'Accept':'application/json'},credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){
  var cs=document.getElementById('ag-c');Object.keys(d.categories||{}).forEach(function(k){var o=document.createElement('option');o.value=k;o.textContent=k.replace(/_/g,' ').replace(/\b\w/g,function(m){return m.toUpperCase()})+' ('+d.categories[k]+')';cs.appendChild(o)});
  var sel=document.getElementById('ag-p');Object.keys(d.providers||{}).forEach(function(k){var o=document.createElement('option');o.value=k;o.textContent=k+' ('+d.providers[k]+')';sel.appendChild(o)});
 }).catch(function(){});
 load(true);
})();
</script>
</section>
