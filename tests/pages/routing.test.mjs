import {test} from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import {createHash} from 'node:crypto';
import worker from '../../edge/worker.js';
const assets={ASSETS:{fetch:async request=>{const url=new URL(request.url);let file='dist'+url.pathname;if(file.endsWith('/'))file+='index.html';try{return new Response(await fs.readFile(file),{headers:{'Content-Type':file.endsWith('.html')?'text/html':'text/plain'}})}catch{return new Response('Not found',{status:404})}}}};
const get=url=>worker.fetch(new Request(url),assets);
const invocationRoutes=JSON.parse(await fs.readFile('dist/_routes.json','utf8'));
const matchesRoute=(path,pattern)=>new RegExp('^'+pattern.split('*').map(part=>part.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')).join('.*')+'$').test(path);
const invokesFunction=url=>{const path=new URL(url).pathname;return invocationRoutes.include.some(pattern=>matchesRoute(path,pattern))&&!invocationRoutes.exclude.some(pattern=>matchesRoute(path,pattern))};
test('scanner paths and static files do not invoke the shared-quota Function',()=>{
 for(const host of ['sergiotech.es','www.sergiotech.es','savetempo.sergiotech.es'])for(const path of ['/.git/config','/.env.aws','/src/.env','/admin/.env','/info.php','/composer.json','/wp-login.php','/missing/','/favicon.ico','/assets/css/styles.css','/savetempo-assets/images/today-es.webp','/apps/savetempo/assets/css/savetempo.css','/_astro/main.hash.js']){
  assert.equal(invokesFunction('https://'+host+path),false,host+path);
 }
 assert.ok(invocationRoutes.include.length+invocationRoutes.exclude.length<=100);
});
test('routing remains enabled for languages, sitemaps, contact and private templates',()=>{
 for(const path of ['/','/?lang=en','/index.php','/index.html','/en/','/en/index.html','/robots.txt','/sitemap.xml','/api/contact','/api/contact/session','/contact.php','/product-content/es/privacy/','/savetempo-sitemap.xml','/savetempo-robots.txt','/preview-savetempo/es/privacy/','/apps/savetempo/privacy/index.php','/es/apps/savetempo/privacy/'])assert.equal(invokesFunction('https://sergiotech.es'+path),true,path);
});
test('every published product URL and its legacy document aliases keep host-aware routing',async()=>{
 const product=JSON.parse(await fs.readFile('src/data/product.json','utf8'));
 for(const locale of ['en','es'])for(const page of Object.values(product.pagePaths)){
  const base=(locale==='es'?'/es':'')+page[locale];
  for(const path of [base,base+'index.php',base+'index.html'])assert.equal(invokesFunction('https://savetempo.sergiotech.es'+path),true,path);
  if(base!=='/')assert.equal(invokesFunction('https://savetempo.sergiotech.es'+base.slice(0,-1)),true,base);
 }
 assert.equal(invokesFunction('https://savetempo.sergiotech.es/es/52-week-savings-challenge/?lang=en'),true);
});
test('keeps both portfolio languages and canonical query URL',async()=>{
 for(const [url,lang]of [['https://sergiotech.es/','es'],['https://sergiotech.es/?lang=en','en']]){const r=await get(url);const html=await r.text();assert.equal(r.status,200);assert.match(html,new RegExp('<html lang="'+lang+'"'));assert.equal(r.headers.get('Content-Language'),lang);assert.ok(!html.includes('<?php'));assert.ok(!html.includes('csrf_token'));assert.match(r.headers.get('Content-Security-Policy'),/sha256-/)}
 for(const url of ['https://sergiotech.es/?lang=es','https://sergiotech.es/index.php','https://www.sergiotech.es/']){const r=await get(url);assert.equal(r.status,301);assert.equal(r.headers.get('Location'),'https://sergiotech.es/')}
});
test('existing Android legal links and Spanish variants redirect to matching published pages',async()=>{
 for(const suffix of ['','privacy/','support/','terms/'])for(const [prefix,locale]of [['/apps/savetempo/',''],['/es/apps/savetempo/','es/']]){
  const r=await get('https://sergiotech.es'+prefix+suffix);assert.equal(r.status,301);const target=r.headers.get('Location');assert.equal(target,'https://savetempo.sergiotech.es/'+locale+suffix);
  const destination=await get(target);assert.equal(destination.status,200);assert.match(await destination.text(),/<h1/);
 }
});
test('maps old language queries and translated challenge slugs',async()=>{
 const r=await get('https://sergiotech.es/apps/savetempo/52-week-savings-challenge/index.php?lang=es');assert.equal(r.headers.get('Location'),'https://savetempo.sergiotech.es/es/reto-ahorro-52-semanas/');
});
test('unknown product pages are real 404 and internal templates cannot be browsed',async()=>{
 assert.equal((await get('https://savetempo.sergiotech.es/missing/')).status,404);assert.equal((await get('https://sergiotech.es/product-content/es/privacy/')).status,404);
});
test('browser-normalized inline styles match CSP and the header stays within Pages limits',async()=>{
 const r=await get('https://sergiotech.es/'),html=await r.text(),csp=r.headers.get('Content-Security-Policy');
 assert.ok(csp.length+27<2000);
 for(const match of html.matchAll(/<style\b[^>]*>([\s\S]*?)<\/style>/g)){const hash=createHash('sha256').update(match[1].replace(/\r\n?/g,'\n')).digest('base64');assert.ok(csp.includes("'sha256-"+hash+"'"))}
 assert.ok(!html.includes('>\">'));
});
test('every generated product page has canonical metadata and assets without PHP',async()=>{
 for(const locale of ['en','es'])for(const suffix of ['','privacy/','support/','terms/',locale==='es'?'reto-ahorro-52-semanas/':'52-week-savings-challenge/',locale==='es'?'reto-ahorro-365-dias/':'365-day-savings-challenge/',locale==='es'?'app-retos-ahorro/':'savings-challenge-app/']){
  const url='https://savetempo.sergiotech.es/'+(locale==='es'?'es/':'')+suffix,r=await get(url),html=await r.text();assert.equal(r.status,200,url);assert.ok(html.includes('rel="canonical" href="'+url+'"'),url);assert.ok(!html.includes('<?'));assert.ok(!html.includes('stEscape'));assert.equal(r.headers.get('Content-Language'),locale);
  for(const match of html.matchAll(/(?:src|href)="(\/savetempo-assets\/[^"?]+)/g))await fs.access('dist'+match[1]);
 }
});
