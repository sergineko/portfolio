import assert from 'node:assert/strict';
import { request as httpRequest } from 'node:http';
import { request as httpsRequest } from 'node:https';

const base = (process.env.SEO_TEST_BASE_URL || 'http://127.0.0.1:18080').replace(/\/$/, '');
const canonicalBase = (process.env.SEO_TEST_CANONICAL_URL || 'https://sergiotech.es').replace(/\/$/, '');
let checks = 0;

async function request(path, options = {}) {
    const url = new URL(base + path);
    const send = url.protocol === 'https:' ? httpsRequest : httpRequest;
    const body = options.body?.toString();
    const headers = { ...options.headers };
    if (options.body instanceof URLSearchParams) {
        headers['Content-Type'] = 'application/x-www-form-urlencoded';
    }
    return new Promise((resolve, reject) => {
        const pending = send(url, { method: options.method || 'GET', headers }, incoming => {
            const chunks = [];
            incoming.on('data', chunk => chunks.push(chunk));
            incoming.on('end', () => resolve({
                response: { status: incoming.statusCode, headers: new Headers(incoming.headers) },
                html: Buffer.concat(chunks).toString('utf8'),
            }));
            incoming.on('error', reject);
        });
        pending.setTimeout(15000, () => pending.destroy(new Error(`Timeout: ${path}`)));
        pending.on('error', reject);
        pending.end(body);
    });
}

async function expectRedirect(path, destination, options = {}) {
    const { response } = await request(path, options);
    assert.equal(response.status, 301, `${path}: expected a permanent redirect`);
    const location = response.headers.get('location');
    assert.ok(location, `${path}: missing redirect destination`);
    assert.ok(!location.startsWith('http:'), `${path}: must not downgrade HTTPS`);
    const parsed = new URL(location, canonicalBase);
    assert.equal(parsed.pathname + parsed.search, destination, `${path}: wrong destination`);
    checks++;
}

for (const headers of [
    {},
    { 'Accept-Language': 'en-US,en;q=0.9', Cookie: 'portfolio_lang=en' },
    { 'Accept-Language': 'es-ES,es;q=0.9', 'CF-IPCountry': 'US' },
]) {
    const { response, html } = await request('/', { headers });
    assert.equal(response.status, 200);
    assert.match(html, /<html lang="es"/);
    assert.ok(html.includes(`rel="canonical" href="${canonicalBase}/"`));
    assert.ok(html.includes('href="/es/apps/savetempo/"'), 'Spanish portfolio must link to Spanish SaveTempo');
    assert.ok(!html.includes('hreflang="es" href="' + canonicalBase + '/?lang=es"'));
    checks++;
}

const english = await request('/?lang=en', { headers: { 'Accept-Language': 'es', Cookie: 'portfolio_lang=es' } });
assert.equal(english.response.status, 200);
assert.match(english.html, /<html lang="en"/);
assert.ok(english.html.includes(`rel="canonical" href="${canonicalBase}/?lang=en"`));
assert.ok(english.html.includes('href="/apps/savetempo/"'));
checks++;

await expectRedirect('/?lang=es', '/');
await expectRedirect('/?lang=es&status=error', '/?status=error');
await expectRedirect('/?lang=EN', '/?lang=en');
await expectRedirect('/?lang[]=es', '/');
await expectRedirect('/index.php?lang=en&status=error', '/?lang=en&status=error');
await expectRedirect('/?lang=es', '/', { method: 'HEAD' });
await expectRedirect('/', '/', { headers: { Host: 'www.sergiotech.es' } });

const routes = [
    ['/', '/'],
    ['/privacy', '/privacy'],
    ['/support', '/support'],
    ['/terms', '/terms'],
    ['/52-week-savings-challenge', '/reto-ahorro-52-semanas'],
    ['/365-day-savings-challenge', '/reto-ahorro-365-dias'],
    ['/savings-challenge-app', '/app-retos-ahorro'],
];
for (const [englishSuffix, spanishSuffix] of routes) {
    const en = '/apps/savetempo' + (englishSuffix === '/' ? '/' : englishSuffix + '/');
    const es = '/es/apps/savetempo' + (spanishSuffix === '/' ? '/' : spanishSuffix + '/');
    await expectRedirect(en.slice(0, -1) + '?lang=es', es);
    await expectRedirect(en + '?lang=es', es);
    await expectRedirect(en + 'index.php?lang=es', es);
    await expectRedirect(en + '?lang=en', en);
    await expectRedirect(en.slice(0, -1), en);
    await expectRedirect(es.slice(0, -1), es);
    await expectRedirect(es + 'index.php', es);
}

const { response: sitemapResponse, html: sitemap } = await request('/sitemap.xml');
assert.equal(sitemapResponse.status, 200);
const locations = [...sitemap.matchAll(/<loc>([^<]+)<\/loc>/g)].map(match => match[1]);
assert.equal(locations.length, 16, 'Sitemap must contain only the two portfolio and fourteen SaveTempo canonical URLs');
assert.equal(new Set(locations).size, 16);
assert.ok(!locations.some(url => url.includes('lang=es')));

for (const url of locations) {
    const parsed = new URL(url);
    const { response, html } = await request(parsed.pathname + parsed.search);
    assert.equal(response.status, 200, `${url}: sitemap URL must not redirect`);
    assert.ok(html.includes(`rel="canonical" href="${url}"`), `${url}: canonical must match sitemap`);
    assert.ok(!/\bnoindex\b/i.test(response.headers.get('x-robots-tag') || ''));
    assert.ok(!/<meta[^>]+name="robots"[^>]+content="[^"]*noindex/i.test(html));
    assert.ok(response.headers.get('content-security-policy')?.includes("connect-src 'self';"), `${url}: connections must stay on the website origin`);
    for (const script of html.matchAll(/<script\b[^>]*\bsrc="([^"]+)"/g)) {
        assert.equal(new URL(script[1], url).origin, parsed.origin, `${url}: external scripts must not be embedded`);
    }
    const expectedLanguage = parsed.pathname.startsWith('/es/') || parsed.pathname === '/' && !parsed.search ? 'es' : 'en';
    assert.ok(html.includes(`<html lang="${expectedLanguage}"`), `${url}: wrong language`);
    const alternates = [...html.matchAll(/rel="alternate" hreflang="(es|en)" href="([^"]+)"/g)];
    assert.equal(alternates.length, 2, `${url}: both language alternates are required`);
    for (const alternate of alternates) {
        assert.ok(locations.includes(alternate[2]), `${url}: alternate must be a canonical sitemap URL`);
    }
    checks++;
}

for (const path of ['/does-not-exist-seo-check/', '/apps/savetempo/does-not-exist/', '/nginx.template.conf', '/apps/savetempo/config/product.php']) {
    const { response } = await request(path);
    assert.equal(response.status, 404, `${path}: must not serve a portfolio duplicate`);
    checks++;
}

for (const language of ['en', 'es']) {
    const { response } = await request('/contact.php', { method: 'POST', body: new URLSearchParams({ language }) });
    assert.equal(response.status, 303);
    assert.equal(response.headers.get('location'), language === 'en' ? '/?lang=en&status=error#contacto' : '/?status=error#contacto');
    checks++;
}

console.log(`SEO HTTP tests: PASS (${checks} checks)`);
