const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { test } = require('node:test');
const { JSDOM } = require('jsdom');
const jquery = require('jquery');

const root = path.join(__dirname, '..');
const cookies = { essential: ['necessary'], statistics: ['analytics'], external_media: ['video'] };

function fixture() {
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

function setup(t, script, { settings = {}, savedConsent, narrow = false } = {}) {
    const dom = new JSDOM(fixture(), { url: 'https://consent.test/', runScripts: 'dangerously' });
    const { window } = dom;
    t.after(() => window.close());
    window.matchMedia = () => ({ matches: narrow });
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
    window.RRZELegal.init({
        cookies: structuredClone(cookies), cookiePath: '/', blockContent: '1', boxLayoutAdvanced: '1',
        animation: '', ignorePreSelectStatus: '', ...settings,
    }, services, {}, {});
    return {
        window,
        document: window.document,
        api: window.RRZELegal,
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

function assertRefused(ctx) {
    assert.deepEqual(ctx.saved().consents, { essential: ['necessary'] });
    assert.equal(ctx.saved().uid, 'anonymous');
    assert.equal(ctx.overlay.style.display, 'none');
    assert.equal(ctx.overlay.classList.contains('show-cookie-box'), false);
    assert.equal(ctx.document.querySelector('#RRZELegalBanner').getAttribute('aria-modal'), 'false');
    assert.equal(ctx.document.body.classList.contains('rrzelegal-position-fix'), false);
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
