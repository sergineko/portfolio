import {test} from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import {createHash} from 'node:crypto';
import worker from '../../edge/worker.js';
const assets={ASSETS:{fetch:async request=>{const url=new URL(request.url);let file='dist'+url.pathname;if(file.endsWith('/'))file+='index.html';try{return new Response(await fs.readFile(file),{headers:{'Content-Type':file.endsWith('.html')?'text/html':'text/plain'}})}catch{return new Response('Not found',{status:404})}}}};
const get=url=>worker.fetch(new Request(url),assets);
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
