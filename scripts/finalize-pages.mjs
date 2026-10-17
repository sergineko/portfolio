import fs from 'node:fs/promises';
import path from 'node:path';
import {createHash} from 'node:crypto';
import {build} from 'esbuild';
import product from '../src/data/product.json' with {type:'json'};
const dist=new URL('../dist/',import.meta.url);
// Retain the old URL for visitors whose HTML still references the public script.
await fs.mkdir(new URL('assets/js/',dist),{recursive:true});
await fs.copyFile(new URL('../src/scripts/main.js',import.meta.url),new URL('assets/js/main.js',dist));
const hashes={script:new Set(),style:new Set()};
async function scan(dir){for(const entry of await fs.readdir(dir,{withFileTypes:true})){const file=new URL(entry.name+(entry.isDirectory()?'/':''),dir);if(entry.isDirectory())await scan(file);else if(entry.name.endsWith('.html')){const html=(await fs.readFile(file,'utf8')).replace(/\r\n?/g,'\n');await fs.writeFile(file,html);for(const match of html.matchAll(/<(script|style)\b([^>]*)>([\s\S]*?)<\/\1>/g)){if(match[3].trim())hashes[match[1]].add("'sha256-"+createHash('sha256').update(match[3]).digest('base64')+"'")}}}}
await scan(dist);
const CSP="default-src 'self'; base-uri 'self'; connect-src 'self' https://challenges.cloudflare.com; font-src 'self'; form-action 'self'; frame-ancestors 'none'; frame-src https://challenges.cloudflare.com; img-src 'self' data:; object-src 'none'; script-src 'self' https://challenges.cloudflare.com "+[...hashes.script].join(' ')+"; style-src 'self' "+[...hashes.style].join(' ')+"; upgrade-insecure-requests";
if(CSP.length+27>2000)throw new Error('CSP exceeds the Cloudflare Pages header limit');
await fs.writeFile(new URL('../edge/security.generated.js',import.meta.url),'export const CSP='+JSON.stringify(CSP)+';\n');
await build({entryPoints:[new URL('../edge/worker.js',import.meta.url).pathname.replace(/^\/(\w:)/,'$1')],outfile:new URL('_worker.js',dist).pathname.replace(/^\/(\w:)/,'$1'),bundle:true,format:'esm',platform:'browser',target:'es2022',minify:true});
// Only documents, redirects and contact need the host/language router. Unknown
// paths must reach Pages' static 404 without spending the shared Workers quota.
const functionRoutes=new Set(['/', '/index.php', '/index.html', '/en/', '/en/index.html', '/robots.txt', '/sitemap.xml', '/api/contact', '/api/contact/*', '/contact.php', '/apps/savetempo', '/apps/savetempo/*', '/es/apps/savetempo', '/es/apps/savetempo/*', '/product-content/*', '/savetempo-sitemap.xml', '/savetempo-robots.txt', '/preview-savetempo/*']);
const productSlugs=new Set(Object.values(product.pagePaths).flatMap(page=>Object.values(page)).filter(page=>page!=='/').map(page=>page.replace(/\/$/,'')));
// Both translated slugs remain routed so existing ?lang= links can redirect.
for(const prefix of ['', '/es'])for(const slug of productSlugs){const base=prefix+slug;functionRoutes.add(base);functionRoutes.add(base+'/*')}
for(const alias of ['/es','/es/','/es/index.php','/es/index.html'])functionRoutes.add(alias);
const invocationRoutes={version:1,include:[...functionRoutes],exclude:['/_astro/*','/assets/*','/savetempo-assets/*','/apps/savetempo/assets/*','/favicon.ico','/favicon-192x192.png','/sergiotech-favicon-192.png','/site.webmanifest']};
if(invocationRoutes.include.length+invocationRoutes.exclude.length>100)throw new Error('Pages invocation routes exceed the 100-rule limit');
await fs.writeFile(new URL('_routes.json',dist),JSON.stringify(invocationRoutes,null,2));
await fs.writeFile(new URL('_headers',dist),`/*\n  Content-Security-Policy: ${CSP}\n  X-Content-Type-Options: nosniff\n  X-Frame-Options: DENY\n  Referrer-Policy: strict-origin-when-cross-origin\n  Permissions-Policy: camera=(), microphone=(), geolocation=()\n/_astro/*\n  Cache-Control: public, max-age=31536000, immutable\n/assets/*\n  Cache-Control: public, max-age=86400\n/savetempo-assets/*\n  Cache-Control: public, max-age=86400\n`);
const urlset=urls=>'<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'+urls.join('')+'</urlset>';
const entry=(url,en,es)=>`<url><loc>${url}</loc><xhtml:link rel="alternate" hreflang="en" href="${en}"/><xhtml:link rel="alternate" hreflang="es" href="${es}"/></url>`;
await fs.writeFile(new URL('sitemap.xml',dist),urlset([entry('https://sergiotech.es/','https://sergiotech.es/?lang=en','https://sergiotech.es/'),entry('https://sergiotech.es/?lang=en','https://sergiotech.es/?lang=en','https://sergiotech.es/')]));
await fs.writeFile(new URL('savetempo-sitemap.xml',dist),urlset(Object.values(product.pagePaths).flatMap(p=>{const en='https://savetempo.sergiotech.es'+p.en,es='https://savetempo.sergiotech.es/es'+p.es;return[entry(en,en,es),entry(es,en,es)]})));
for(const [name,origin]of [['robots.txt','https://sergiotech.es'],['savetempo-robots.txt','https://savetempo.sergiotech.es']])await fs.writeFile(new URL(name,dist),'User-agent: *\nAllow: /\nSitemap: '+origin+'/sitemap.xml\n');
console.log(`Pages bundle ready: 16 canonical pages, ${hashes.script.size+hashes.style.size} CSP hashes, contact endpoint and legacy redirects.`);
