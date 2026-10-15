import {contact} from './contact.js';
import {CSP} from './security.generated.js';
const portfolioHost='sergiotech.es',productHost='savetempo.sergiotech.es';
const routes={en:['','privacy','support','terms','52-week-savings-challenge','365-day-savings-challenge','savings-challenge-app'],es:['','privacy','support','terms','reto-ahorro-52-semanas','reto-ahorro-365-dias','app-retos-ahorro']};
export function legacyProduct(url){
 const match=url.pathname.match(/^\/(es\/)?apps\/savetempo(?:\/(.*?))?\/?$/);
 if(!match)return null;
 let language=match[1]?'es':'en';
 if(['en','es'].includes(url.searchParams.get('lang')))language=url.searchParams.get('lang');
 let suffix=(match[2]??'').replace(/(?:\/)?index\.php$/,'').replace(/\/$/,'');
 const index=Math.max(routes.en.indexOf(suffix),routes.es.indexOf(suffix));
 if(index<0)return null;
 suffix=routes[language][index];
 const target=new URL('https://'+productHost+(language==='es'?'/es/':'/')+(suffix?suffix+'/':''));
 for(const [key,value]of url.searchParams)if(key!=='lang')target.searchParams.append(key,value);
 target.hash=url.hash;return target;
}
const redirect=url=>Response.redirect(url,301);
async function asset(request,env,path,language,status){
 const url=new URL(request.url);url.pathname=path;url.search='';
 const response=await env.ASSETS.fetch(new Request(url,{method:request.method==='HEAD'?'HEAD':'GET'}));
 const headers=new Headers(response.headers);
 headers.set('Content-Security-Policy',CSP);headers.set('X-Content-Type-Options','nosniff');headers.set('Referrer-Policy','strict-origin-when-cross-origin');headers.set('X-Frame-Options','DENY');
 if(language)headers.set('Content-Language',language);
 if(request.url.includes('.pages.dev'))headers.set('X-Robots-Tag','noindex, nofollow');
 return new Response(response.body,{status:status??response.status,headers});
}
export default {async fetch(request,env){
 const url=new URL(request.url),host=url.hostname,path=url.pathname;
 if(host==='www.'+portfolioHost)return redirect('https://'+portfolioHost+path+url.search);
 if(path.startsWith('/api/contact')||path==='/contact.php'){
  if(host===productHost)return new Response('Not found',{status:404});
  if(!['/api/contact','/api/contact/session','/contact.php'].includes(path))return new Response('Not found',{status:404});
  return contact(request,env);
 }
 if(request.method!=='GET'&&request.method!=='HEAD')return new Response('Method not allowed',{status:405,headers:{Allow:'GET, HEAD'}});
 if(path.startsWith('/savetempo-assets/')||path.startsWith('/apps/savetempo/assets/'))return env.ASSETS.fetch(request);
 const legacy=legacyProduct(url);if(legacy)return redirect(legacy);
 if(host===productHost||((host.endsWith('.pages.dev')||host==='localhost'||host==='127.0.0.1')&&path.startsWith('/preview-savetempo/'))){
  let publicPath=path.replace(/^\/preview-savetempo/,'').replace(/index\.(php|html)$/,'');
  let language=publicPath.startsWith('/es/')?'es':'en';
  let suffix=publicPath.replace(/^\/es(?=\/)/,'').replace(/^\//,'').replace(/\/$/,'');
  if(url.searchParams.has('lang')){
   language=url.searchParams.get('lang')==='es'?'es':'en';
   const index=Math.max(routes.en.indexOf(suffix),routes.es.indexOf(suffix));
   if(index>=0){const target=new URL(url);target.pathname=(language==='es'?'/es/':'/')+(routes[language][index]?routes[language][index]+'/':'');target.searchParams.delete('lang');return redirect(target)}
  }
  if(path==='/robots.txt')return asset(request,env,'/savetempo-robots.txt');
  if(path==='/sitemap.xml')return asset(request,env,'/savetempo-sitemap.xml');
  if(routes[language].includes(suffix)){
   const canonical=(language==='es'?'/es/':'/')+(suffix?suffix+'/':'');
   if(!path.startsWith('/preview-savetempo/')&&path!==canonical)return redirect('https://'+productHost+canonical+url.search);
   return asset(request,env,'/product-content/'+language+'/'+(suffix?suffix+'/':''),language);
  }
  if(path.startsWith('/assets/')||path==='/favicon.ico')return env.ASSETS.fetch(request);
  return asset(request,env,'/404.html',undefined,404);
 }
 if(path.startsWith('/product-content/')||path==='/savetempo-sitemap.xml'||path==='/savetempo-robots.txt')return new Response('Not found',{status:404});
 if(path==='/en/'||path==='/en/index.html')return redirect(new URL('/?lang=en',url));
 if(path==='/'||path==='/index.php'||path==='/index.html'){
  const language=url.searchParams.get('lang')==='en'?'en':'es';
  if(path!=='/'||(url.searchParams.has('lang')&&url.searchParams.get('lang')!=='en')){
   url.pathname='/';if(language!=='en')url.searchParams.delete('lang');return redirect(url);
  }
  return asset(request,env,language==='en'?'/en/':'/',language);
 }
 return env.ASSETS.fetch(request);
}};
