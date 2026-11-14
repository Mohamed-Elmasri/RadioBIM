const V='rb-v1', SHELL=['/','/guide.html','/favicon.svg','/icon-192.png','/manifest.webmanifest'];
self.addEventListener('install',e=>{e.waitUntil(caches.open(V).then(c=>c.addAll(SHELL)).catch(()=>{}));self.skipWaiting();});
self.addEventListener('activate',e=>{e.waitUntil(caches.keys().then(ks=>Promise.all(ks.filter(k=>k!==V).map(k=>caches.delete(k)))).then(()=>self.clients.claim()));});
const fresh=req=>fetch(req).then(r=>{if(r&&r.ok){const c=r.clone();caches.open(V).then(x=>x.put(req,c));}return r;}).catch(()=>caches.match(req,{ignoreSearch:true}).then(r=>r||caches.match('/')));
self.addEventListener('fetch',e=>{const r=e.request; if(r.method!=='GET') return; const u=new URL(r.url);
 if(r.mode==='navigate'||u.pathname.endsWith('.html')||(u.hostname==='raw.githubusercontent.com'&&u.pathname.endsWith('/data.json'))||(u.origin===location.origin&&u.pathname.endsWith('data.json'))){e.respondWith(fresh(r));return;}
 if(u.origin===location.origin&&/\.(png|svg|webp|ico|webmanifest)$/.test(u.pathname)){e.respondWith(caches.match(r).then(c=>c||fresh(r)));}
});
