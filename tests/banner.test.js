const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const { JSDOM } = require('jsdom');
const jquery = require('jquery');

const root = path.join(__dirname, '..');
const cookies = { essential: ['necessary'], statistics: ['analytics'], external_media: ['video'] };

/**
 * Builds the consent markup and a blocked optional-content placeholder.
 * @returns {string} HTML for a page with a two-panel consent banner.
 */
function fixture() {
    /** @param {boolean} includeEssential Whether to include the required group. @returns {string} Checkbox markup. */
    const groups = (includeEssential = true) => Object.keys(cookies).filter(group => includeEssential || group !== 'essential').map(group => `
        <input type="checkbox" name="cookieGroup[]" value="${group}" checked
            ${group === 'essential' ? 'disabled' : ''} data-rrzelegal-cookie-checkbox>
    `).join('');
    const details = Object.entries(cookies).filter(([group]) => group !== 'essential').map(([group, ids]) => `
        <input type="checkbox" name="cookies[${group}][]" value="${ids[0]}"
            data-cookie-group="${group}" checked ${group === 'essential' ? 'disabled' : ''}>
    `).join('');
    return `<!doctype html><html><body>
        <button id="outside">Outside banner</button>
        <div id="RRZELegalBanner" class="RRZELegal" aria-modal="true">
            <div class="middle-center" style="display: none;">
                <div class="_rrzelegal-box-wrap"><div class="_rrzelegal-box">
                    <div class="cookie-box"><div class="container">
                        ${groups()}
                        <a href="#" id="BannerSaveButton" data-cookie-accept>Save</a>
                        <a href="#" data-cookie-accept-all>Accept all</a>
                        <a href="#" data-cookie-refuse>Refuse</a>
                        <a href="#" data-cookie-individual>Preferences</a>
                    </div></div>
                    <div class="cookie-preference" aria-hidden="true"><div class="container not-visible">
                        ${groups(false)}${details}
                        <a href="#" id="CookiePrefSave" data-cookie-accept>Save</a>
                        <a href="#" data-cookie-refuse>Refuse</a>
                        <a href="#" data-cookie-back>Back</a>
                    </div></div>
                </div></div>
            </div>
        </div>
        <div data-rrzelegal-cookie-type="cookie-group" data-rrzelegal-cookie-id="statistics"><script type="text/plain">${Buffer.from('<span id="optional-content">Optional content</span>').toString('base64')}</script></div>
    </body></html>`;
}

/**
 * Loads either source or production code into an isolated consent page.
 * jsdom has no layout engine, so non-collapsed elements receive synthetic geometry;
 * visibility styles are still evaluated by the application using getComputedStyle.
 * @param {import('node:test').TestContext} t Test lifecycle context.
 * @param {string} script Repository-relative JavaScript file.
 * @param {object} [options] Settings, stored consent, viewport and setup hook.
 * @returns {object} Window, consent API and helpers for user interactions.
 */
function setup(t, script, { settings = {}, savedConsent, narrow = false, beforeInit } = {}) {
    const dom = new JSDOM(fixture(), { url: 'https://consent.test/', runScripts: 'dangerously' });
    const { window } = dom;
    t.after(() => window.close());
    window.matchMedia = () => ({ matches: narrow });
    /** @returns {object} Synthetic geometry for jsdom's layout-free DOM. */
    window.HTMLElement.prototype.getBoundingClientRect = function () {
        const collapsed = this.classList.contains('not-visible') || this.style.height === '0px';
        return { x: 0, y: 0, top: 0, left: 0, right: 100, bottom: collapsed ? 0 : 40,
            width: 100, height: collapsed ? 0 : 40 };
    };
    window.jQuery = jquery(window);
    window.jQuery.fx.off = true;
    window.executed = [];
    if (savedConsent) {
        window.document.cookie = 'rrze-legal-consent=' + encodeURIComponent(JSON.stringify({
            consents: savedConsent, version: '1', uid: 'existing-visitor', domainPath: '/',
        })) + '; path=/';
    }
    window.eval(fs.readFileSync(path.join(root, script), 'utf8'));
    const services = Object.fromEntries(Object.entries(cookies).map(([group, ids]) => [group, {
        [ids[0]]: {
            settings: {},
            optInJS: Buffer.from(`<script>window.executed.push('${ids[0]}:in')</script>`).toString('base64'),
            optOutJS: Buffer.from(`<script>window.executed.push('${ids[0]}:out')</script>`).toString('base64'),
        },
    }]));
    let saves = 0;
    window.document.addEventListener('rrzelegal-cookie-consent-saved', () => saves++);
    if (beforeInit) {
        beforeInit(window, services);
    }
    window.RRZELegal.init({
        cookies: structuredClone(cookies), cookiePath: '/', blockContent: '1', boxLayoutAdvanced: '1',
        animation: '', ignorePreSelectStatus: '', ...settings,
    }, services, {}, {});
    return {
        window,
        document: window.document,
        api: window.RRZELegal,
        services,
        saves: () => saves,
        saved: () => {
            const value = window.document.cookie.split('; ').find(cookie => cookie.startsWith('rrze-legal-consent='));
            return value && JSON.parse(decodeURIComponent(value.slice(value.indexOf('=') + 1)));
        },
        key: (key = 'Escape', target = window.document.activeElement, extra = {}) => {
            const event = new window.KeyboardEvent('keydown', { key, bubbles: true, cancelable: true, ...extra });
            target.dispatchEvent(event);
            return event;
        },
        click: selector => window.document.querySelector(selector).click(),
        overlay: window.document.querySelector('#RRZELegalBanner > div'),
    };
}

/**
 * Verifies that closing the dialog fully releases its modal state.
 * @param {object} ctx Fixture context, possibly with a removed banner.
 * @returns {void}
 */
function assertClosed(ctx) {
    assert.equal(ctx.overlay.style.display, 'none');
    assert.equal(ctx.overlay.classList.contains('show-cookie-box'), false);
    const banner = ctx.document.querySelector('#RRZELegalBanner');
    if (banner) {
        assert.equal(banner.getAttribute('aria-modal'), 'false');
    }
    assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), false);
}

/**
 * Verifies that refusal saves only required consent and releases the page.
 * @param {object} ctx Fixture context.
 * @returns {void}
 */
function assertRefused(ctx) {
    assert.deepEqual(ctx.saved().consents, { essential: ['necessary'] });
    assert.equal(ctx.saved().uid, 'anonymous');
    assertClosed(ctx);
}

for (const script of ['src/javascript/frontend/banner.js', 'build/rrze-legal.js']) {
    test(`${script}: Escape refuses all preselected optional cookies and leaves optional code blocked`, t => {
        const ctx = setup(t, script);
        assert.equal(ctx.key().defaultPrevented, true);
        assertRefused(ctx);
        assert.deepEqual(Array.from(ctx.window.executed), ['necessary:in']);
        assert.equal(ctx.document.querySelector('#optional-content'), null);
        assert.equal(ctx.saves(), 1);
    });

    test(`${script}: Escape works with an obscured banner and focus outside it`, t => {
        const ctx = setup(t, script);
        ctx.document.querySelector('._rrzelegal-box-wrap').style.visibility = 'hidden';
        const outside = ctx.document.querySelector('#outside');
        outside.focus();
        outside.addEventListener('keydown', event => event.stopPropagation());
        ctx.key('Escape', outside);
        assertRefused(ctx);
    });

    for (const narrow of [false, true]) {
        test(`${script}: Escape refuses from ${narrow ? 'narrow' : 'wide'} preferences`, t => {
            const ctx = setup(t, script, { narrow });
            ctx.click('[data-cookie-individual]');
            assert.equal(ctx.document.querySelector('.cookie-preference').getAttribute('aria-hidden'), 'false');
            ctx.key();
            assertRefused(ctx);
        });
    }

    test(`${script}: hidden banner and unrelated keys do not change consent`, t => {
        const ctx = setup(t, script, { settings: { showBanner: '' } });
        assert.equal(ctx.key().defaultPrevented, false);
        assert.equal(ctx.saved(), undefined);
        ctx.api.showBanner();
        assert.equal(ctx.key('Enter').defaultPrevented, false);
        assert.equal(ctx.saved(), undefined);
        ctx.api.hideBanner();
        assert.equal(ctx.key().defaultPrevented, false);
        assert.equal(ctx.saves(), 0);
    });

    test(`${script}: Escape is inactive after closing and works again on reopening`, async t => {
        const ctx = setup(t, script, { settings: { animation: '1' } });
        ctx.key();
        assertRefused(ctx);
        assert.equal(ctx.key().defaultPrevented, false);
        assert.equal(ctx.saves(), 1);
        ctx.api.showBanner();
        await new Promise(resolve => ctx.window.setTimeout(resolve, 1100));
        assert.equal(ctx.overlay.classList.contains('show-cookie-box'), true);
        assert.notEqual(ctx.overlay.style.display, 'none');
        ctx.key('Escape', ctx.document.body, { repeat: true });
        assertRefused(ctx);
        assert.equal(ctx.saves(), 2);
    });

    test(`${script}: Escape withdraws a previous optional consent only while open`, t => {
        const ctx = setup(t, script, { savedConsent: cookies });
        assert.equal(ctx.key().defaultPrevented, false);
        assert.deepEqual(ctx.saved().consents, cookies);
        ctx.api.showBanner();
        ctx.key();
        assertRefused(ctx);
        assert.ok(ctx.window.executed.includes('analytics:out'));
        assert.ok(ctx.window.executed.includes('video:out'));
    });

    test(`${script}: refusal button keeps the same essential-only behavior`, t => {
        const ctx = setup(t, script);
        ctx.click('[data-cookie-refuse]');
        assertRefused(ctx);
        assert.equal(ctx.saves(), 1);
    });

    test(`${script}: explicit acceptance still saves optional consent`, t => {
        const ctx = setup(t, script);
        ctx.click('[data-cookie-accept-all]');
        assert.deepEqual(ctx.saved().consents, cookies);
        assert.equal(ctx.overlay.style.display, 'none');
        assert.equal(ctx.key().defaultPrevented, false);
        assert.ok(ctx.window.executed.includes('analytics:in'));
    });
}

/**
 * Allows the visibility monitor to observe a browser/extension change.
 * @param {Window} window Fixture window that owns the timer.
 * @returns {Promise<void>} Resolves after one monitoring interval.
 */
function waitForVisibilityCheck(window) {
    return new Promise(resolve => window.setTimeout(resolve, 300));
}

for (const script of ['src/javascript/frontend/banner.js', 'build/rrze-legal.js']) {
    test(`${script}: a visible banner locks scrolling until closed`, async t => {
        const ctx = setup(t, script);
        await waitForVisibilityCheck(ctx.window);
        assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), true);
        ctx.api.hideBanner();
        assertClosed(ctx);
        assert.equal(ctx.saved(), undefined);
    });

    for (const selector of ['#RRZELegalBanner', '.cookie-box', '._rrzelegal-box-wrap']) {
        test(`${script}: missing ${selector} during initialization does not lock the page`, t => {
            const ctx = setup(t, script, {
                beforeInit: window => window.document.querySelector(selector).remove(),
            });
            assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), false);
            assert.equal(ctx.api.showBanner(), false);
            assert.doesNotThrow(() => ctx.api.hideBanner());
            assert.equal(ctx.saved(), undefined);
            assert.deepEqual(Array.from(ctx.window.executed), []);
        });
    }

    for (const selector of ['#RRZELegalBanner', '.cookie-box', '.cookie-box .container']) {
        test(`${script}: CSS-hidden ${selector} never retains a scroll lock`, t => {
            const ctx = setup(t, script, {
                beforeInit: window => {
                    const style = window.document.createElement('style');
                    style.textContent = `${selector} { display: none !important; }`;
                    window.document.head.appendChild(style);
                },
            });
            assertClosed(ctx);
            assert.equal(ctx.saved(), undefined);
            assert.deepEqual(Array.from(ctx.window.executed), []);
        });
    }

    test(`${script}: a banner without dismissal controls does not lock the page`, t => {
        const ctx = setup(t, script, {
            beforeInit: window => window.document.querySelectorAll(
                '[data-cookie-accept], [data-cookie-accept-all], [data-cookie-refuse]'
            ).forEach(element => element.remove()),
        });
        assertClosed(ctx);
        assert.equal(ctx.saved(), undefined);
    });

    test(`${script}: a missing save button falls back to another visible action`, t => {
        const ctx = setup(t, script, {
            beforeInit: window => window.document.querySelector('#BannerSaveButton').remove(),
        });
        assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), true);
        assert.equal(ctx.document.activeElement.hasAttribute('data-cookie-accept-all'), true);
        ctx.click('[data-cookie-refuse]');
        assertRefused(ctx);
    });

    for (const rule of [
        '#RRZELegalBanner { display: none !important; }',
        'body { visibility: hidden; }',
        '.cookie-box .container { opacity: 0; }',
    ]) {
        test(`${script}: later CSS hiding releases the overlay without granting consent (${rule})`, async t => {
            const ctx = setup(t, script);
            const style = ctx.document.createElement('style');
            ctx.document.head.appendChild(style);
            // CSSOM changes do not require a DOM mutation, as with extension styles.
            style.sheet.insertRule(rule);
            await waitForVisibilityCheck(ctx.window);
            assertClosed(ctx);
            assert.equal(ctx.saved(), undefined);
            assert.equal(ctx.saves(), 0);
            assert.deepEqual(Array.from(ctx.window.executed), []);
        });
    }

    test(`${script}: removing the banner releases scrolling automatically`, async t => {
        const ctx = setup(t, script);
        ctx.document.querySelector('#RRZELegalBanner').remove();
        await waitForVisibilityCheck(ctx.window);
        assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), false);
        assert.equal(ctx.key().defaultPrevented, false);
        assert.equal(ctx.saved(), undefined);
        assert.doesNotThrow(() => ctx.api.hideBanner());
        assert.doesNotThrow(() => ctx.api.hideBanner());
    });

    test(`${script}: hideBanner immediately releases scrolling after panel removal`, t => {
        const ctx = setup(t, script);
        ctx.document.querySelector('.cookie-box').remove();
        assert.doesNotThrow(() => ctx.api.hideBanner());
        assertClosed(ctx);
        assert.equal(ctx.key().defaultPrevented, false);
    });

    for (const selector of ['[data-cookie-accept]', '[data-cookie-accept-all]']) {
        test(`${script}: invalid service code cannot keep the dialog open on ${selector}`, t => {
            const ctx = setup(t, script);
            ctx.services.statistics.analytics.optInJS = '%%%';
            assert.throws(() => ctx.window.jQuery(selector).first().trigger('click'), /invalid characters/);
            assertClosed(ctx);
            assert.equal(ctx.key().defaultPrevented, false);
        });
    }

    test(`${script}: a removed blocked-content payload cannot prevent closing`, t => {
        const ctx = setup(t, script);
        ctx.document.querySelector('[data-rrzelegal-cookie-type="cookie-group"] script').remove();
        assert.throws(() => ctx.window.jQuery('[data-cookie-accept-all]').trigger('click'), /innerHTML/);
        assertClosed(ctx);
    });

    test(`${script}: refusal releases scrolling even when opt-out code fails`, t => {
        const ctx = setup(t, script, { savedConsent: cookies });
        ctx.api.showBanner();
        ctx.services.statistics.analytics.optOutJS = '%%%';
        assert.throws(() => ctx.window.jQuery('[data-cookie-refuse]').first().trigger('click'), /invalid characters/);
        assertClosed(ctx);
    });

    test(`${script}: Escape releases scrolling even when saving the cookie throws`, t => {
        const ctx = setup(t, script);
        const errors = [];
        ctx.window.addEventListener('error', event => { errors.push(event.error); event.preventDefault(); });
        Object.defineProperty(ctx.document, 'cookie', {
            configurable: true,
            /** @returns {string} No persisted cookies. */
            get() { return ''; },
            /** @param {string} value Rejected cookie. @returns {void} */
            set(value) { throw new Error('Cookie storage unavailable'); },
        });
        ctx.key();
        assert.equal(errors.length, 1);
        assert.match(errors[0].message, /Cookie storage unavailable/);
        assertClosed(ctx);
        assert.deepEqual(Array.from(ctx.window.executed), []);
    });

    test(`${script}: silently blocked cookie storage still permits refusal`, t => {
        const ctx = setup(t, script);
        Object.defineProperty(ctx.document, 'cookie', {
            configurable: true,
            /** @returns {string} No persisted cookies. */
            get() { return ''; },
            /** @param {string} value Ignored cookie. @returns {void} */
            set(value) {},
        });
        ctx.click('[data-cookie-refuse]');
        assertClosed(ctx);
        assert.deepEqual(Array.from(ctx.window.executed), []);
    });

    for (const narrow of [false, true]) {
        test(`${script}: individual preferences remain usable with monitoring (${narrow ? 'mobile' : 'desktop'})`, async t => {
            const ctx = setup(t, script, { narrow });
            ctx.api.hideBanner();
            assert.equal(ctx.api.openCookiePreference(), true);
            await waitForVisibilityCheck(ctx.window);
            assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), true);
            assert.equal(ctx.document.querySelector('.cookie-preference').getAttribute('aria-hidden'), 'false');
            ctx.key();
            assertRefused(ctx);
        });
    }

    test(`${script}: monitoring checks the active preferences panel`, async t => {
        const ctx = setup(t, script);
        ctx.click('[data-cookie-individual]');
        ctx.document.querySelector('.cookie-box').style.display = 'none';
        await waitForVisibilityCheck(ctx.window);
        assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), true);
        ctx.document.querySelector('.cookie-preference').remove();
        await waitForVisibilityCheck(ctx.window);
        assertClosed(ctx);
        assert.equal(ctx.saved(), undefined);
    });

    test(`${script}: closing detaches the active focus trap even from a removed panel`, t => {
        const ctx = setup(t, script);
        ctx.click('[data-cookie-individual]');
        const panel = ctx.document.querySelector('.cookie-preference');
        const controls = Array.from(panel.querySelectorAll('a, input'));
        controls.forEach(element => Object.defineProperty(element, 'offsetHeight', { value: 20 }));
        const last = controls.at(-1);
        last.focus();
        assert.equal(ctx.key('Tab', last).defaultPrevented, true);
        last.focus();
        panel.remove();
        ctx.api.hideBanner();
        ctx.document.body.appendChild(panel);
        last.focus();
        assert.equal(ctx.key('Tab', last).defaultPrevented, false);
        assertClosed(ctx);
    });

    test(`${script}: an exception during opening leaves the page usable`, t => {
        const ctx = setup(t, script, { settings: { showBanner: '' } });
        ctx.document.querySelector('#BannerSaveButton').focus = () => { throw new Error('Focus failed'); };
        assert.throws(() => ctx.api.showBanner(), /Focus failed/);
        assertClosed(ctx);
        assert.equal(ctx.saved(), undefined);
    });
}

test('without JavaScript, shipped CSS leaves the page unlocked and optional content blocked', t => {
    const dom = new JSDOM(fixture());
    t.after(() => dom.window.close());
    const { document } = dom.window;
    const style = document.createElement('style');
    style.textContent = fs.readFileSync(path.join(root, 'build/rrze-legal.css'), 'utf8');
    document.head.appendChild(style);
    assert.equal(document.body.classList.contains('rrzelegal-position-fix'), false);
    assert.notEqual(dom.window.getComputedStyle(document.body).overflow, 'hidden');
    assert.equal(dom.window.getComputedStyle(document.querySelector('#RRZELegalBanner > div')).display, 'none');
    assert.equal(document.querySelector('#outside').disabled, false);
    assert.equal(document.querySelector('#optional-content'), null);
    assert.equal(document.cookie, '');
});
