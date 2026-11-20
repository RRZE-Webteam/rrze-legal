const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { after, before, test } = require('node:test');
const { webkit } = require('playwright');

const root = path.resolve(__dirname, '../..');
const url = 'https://consent.test/';
const overlay = '#RRZELegalBanner > div';
const wrapper = '#RRZELegalBanner ._rrzelegal-box-wrap';
const assets = {
    '/rrze-legal.css': path.join(root, 'build/rrze-legal.css'),
    '/rrze-legal.js': path.join(root, 'build/rrze-legal.js'),
    '/jquery.js': require.resolve('jquery'),
};
let browser;
before(async () => { browser = await webkit.launch(); });
after(async () => { await browser?.close(); });

/**
 * Opens an isolated first visit with the script template and production assets served over HTTP semantics.
 * @param {import('node:test').TestContext} t Test that owns the browser page.
 * @param {boolean} [mobile] Use a touch viewport and a long list of service descriptions.
 * @returns {Promise<import('playwright').Page>} Page ready to initialize the consent API.
 */
async function openPage(t, mobile = false) {
    const page = await browser.newPage(mobile
        ? { viewport: { width: 375, height: 667 }, isMobile: true, hasTouch: true }
        : { viewport: { width: 1024, height: 768 } });
    t.after(() => page.close());
    page.setDefaultTimeout(3500);
    let html = fs.readFileSync(path.join(__dirname, 'banner.html'), 'utf8');
    if (mobile) {
        html = html.replace('<!-- Additional service details -->',
            '<p>Read the additional service details before choosing your privacy settings.</p>'.repeat(45));
    }
    await page.route('**/*', route => {
        const pathname = new URL(route.request().url()).pathname;
        if (pathname === '/') {
            return route.fulfill({ contentType: 'text/html', body: html });
        }
        if (assets[pathname]) {
            return route.fulfill({ path: assets[pathname] });
        }
        return route.abort();
    });
    await page.goto(url);
    return page;
}

/**
 * Initializes the plugin and records consent side effects on the test page.
 * @param {import('playwright').Page} page Page with an uninitialized production bundle.
 * @returns {Promise<boolean>} Whether the page is locked when initialization returns.
 */
async function initialize(page) {
    return page.evaluate(() => {
        window.saves = 0;
        window.analyticsRuns = 0;
        document.addEventListener('rrzelegal-cookie-consent-saved', () => window.saves++);
        window.RRZELegal.init({
            animation: '', blockContent: '1', boxLayoutAdvanced: '1', cookiePath: '/',
            cookies: { essential: ['necessary'], statistics: ['analytics'] },
        }, {
            essential: { necessary: { settings: {} } },
            statistics: { analytics: {
                settings: {},
                optInJS: btoa('<script>window.analyticsRuns++</script>'),
            } },
        }, {}, {});
        return document.body.classList.contains('rrzelegal-position-fix');
    });
}

/**
 * Captures consent storage, save events and optional service executions together.
 * @param {import('playwright').Page} page Initialized consent page.
 * @returns {Promise<object>} Observable consent state.
 */
function consentState(page) {
    return page.evaluate(() => ({
        cookie: document.cookie, saves: window.saves, analyticsRuns: window.analyticsRuns,
    }));
}

/**
 * Verifies that a visitor can interact with a link underneath the dismissed modal.
 * @param {import('playwright').Page} page Page expected to have released its overlay.
 * @returns {Promise<void>}
 */
async function assertReleased(page) {
    await page.locator(overlay).waitFor({ state: 'hidden' });
    assert.equal(await page.locator('#RRZELegalBanner').getAttribute('aria-modal'), 'false');
    assert.equal(await page.locator('body').evaluate(body => body.classList.contains('rrzelegal-position-fix')), false);
    await page.locator('#article-link').click();
    assert.equal(new URL(page.url()).hash, '#article');
}

for (const [description, css] of [
    ['fully clipped', 'clip-path: inset(50%)'],
    ['offscreen', 'transform: translateX(200vw)'],
]) {
    test(`a ${description} dialog on first visit never locks or saves consent`, async t => {
        const page = await openPage(t);
        const style = await page.addStyleTag({ content: `${wrapper} { ${css}; }` });
        assert.equal(await initialize(page), false);
        await assertReleased(page);
        assert.deepEqual(await consentState(page), { cookie: '', saves: 0, analyticsRuns: 0 });

        await style.evaluate(element => element.remove());
        await page.locator('.rrzelegal-cookie-preference').click();
        await page.waitForFunction(() => document.activeElement.id === 'CookiePrefSave');
        assert.equal(await page.locator(overlay).isVisible(), true);
    });

    test(`a later ${description} preferences dialog releases the page and preserves consent`, async t => {
        const page = await openPage(t);
        assert.equal(await initialize(page), true);
        await page.locator('.cookie-box [data-cookie-accept-all]').click();
        const saved = await consentState(page);
        assert.match(saved.cookie, /rrze-legal-consent=/);
        assert.equal(saved.saves, 1);
        assert.equal(saved.analyticsRuns, 1);
        await page.locator('.rrzelegal-cookie-preference').click();
        await page.waitForFunction(() => document.activeElement.id === 'CookiePrefSave');
        await page.addStyleTag({ content: `${wrapper} { ${css}; }` });
        await assertReleased(page);
        assert.deepEqual(await consentState(page), saved);
    });
}

test('a long mobile dialog stays open and allows scrolling to refuse by touch', async t => {
    const page = await openPage(t, true);
    assert.equal(await initialize(page), true);
    const refuse = page.locator('.cookie-box [data-cookie-refuse]');
    const container = page.locator('.cookie-box .container');
    assert.ok((await refuse.boundingBox()).y >= page.viewportSize().height);
    // Keep the button below the fold for two visibility checks before scrolling.
    await page.waitForTimeout(550);
    await refuse.scrollIntoViewIfNeeded();
    assert.ok(await container.evaluate(element => element.scrollTop > 0));
    assert.equal(await page.evaluate(() => window.scrollY), 0);
    await refuse.tap();
    await assertReleased(page);
    const state = await consentState(page);
    const cookie = JSON.parse(decodeURIComponent(state.cookie.split('=')[1]));
    assert.deepEqual(cookie.consents, { essential: ['necessary'] });
    assert.equal(state.analyticsRuns, 0);
    assert.equal(state.saves, 1);
});

test('a partially covered dialog remains usable across panel changes', async t => {
    const page = await openPage(t);
    assert.equal(await initialize(page), true);
    // Cover the panel midpoint with a separate layer, leaving the four surrounding areas usable.
    const centerCovered = await page.locator('.cookie-box .container').evaluate(container => {
        const rect = container.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        const cover = document.createElement('div');
        cover.style.cssText = `position:fixed;left:${x - 12}px;top:${y - 12}px;width:24px;height:24px;background:white;z-index:2000000`;
        document.body.appendChild(cover);
        return document.elementFromPoint(x, y) === cover;
    });
    assert.ok(centerCovered);
    await page.waitForTimeout(550);
    await page.locator('[data-cookie-individual]').click();
    await page.waitForFunction(() => document.activeElement.id === 'CookiePrefSave');
    await page.locator('[data-cookie-back]').click();
    await page.waitForFunction(() => document.activeElement.id === 'BannerSaveButton');
    await page.keyboard.press('Escape');
    await assertReleased(page);
    assert.equal((await consentState(page)).saves, 1);
});
