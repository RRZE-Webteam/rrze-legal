const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { after, before, test } = require('node:test');
const { webkit } = require('playwright');

const build = path.resolve(__dirname, '../../build');
const styles = fs.readFileSync(path.join(build, 'rrze-legal-admin.css'), 'utf8');
const script = fs.readFileSync(path.join(build, 'rrze-legal-required-fields-dialog.js'), 'utf8');
const confirmUrl = 'https://dashboard.test/confirm';
let browser;
before(async () => { browser = await webkit.launch(); });
after(async () => { await browser?.close(); });

/**
 * Opens the server-rendered dialog in the WordPress dashboard wrapper with the built plugin assets.
 * @param {import('node:test').TestContext} t Test that owns the page.
 * @param {'native'|'without JavaScript'|'showModal throws'} mode Browser behavior to exercise.
 * @returns {Promise<import('playwright').Page>} Isolated dashboard page.
 */
async function openDashboard(t, mode) {
    const page = await browser.newPage({ javaScriptEnabled: mode !== 'without JavaScript' });
    t.after(() => page.close());
    page.setDefaultTimeout(3500);
    await page.setContent(`<!doctype html><html><head><style>${styles}</style></head>
        <body class="rrze-legal-ack-pending">
            <a href="#wpwrap" id="skip-link">Skip to dashboard</a>
            <div id="wpwrap">
                <main><button id="dashboard-action">Dashboard action</button></main>
                <dialog open id="rrze-legal-required-dialog" class="rrze-legal-required-dialog" aria-labelledby="rrze-legal-dialog-title">
                    <h2 id="rrze-legal-dialog-title" tabindex="-1">Legal information still incomplete</h2>
                    <form class="rrze-legal-dialog-form" method="post" action="${confirmUrl}">
                        <label><input type="checkbox" name="confirm" value="1" required> I have read this notice.</label>
                        <button>Confirm and return to the dashboard</button>
                    </form>
                </dialog>
            </div>
        </body></html>`);
    if (mode === 'showModal throws') {
        await page.evaluate(() => {
            HTMLDialogElement.prototype.showModal = () => { throw new DOMException('Cannot open dialog'); };
        });
    }
    await page.addScriptTag({ content: script });
    return page;
}

test('the native dialog holds dashboard focus and stays open on Escape', async t => {
    const page = await openDashboard(t, 'native');
    const modal = page.locator('dialog:modal');
    assert.ok(await modal.isVisible());
    assert.ok(await page.locator('#rrze-legal-dialog-title:focus').count());
    await page.locator('#dashboard-action').evaluate(button => button.focus());
    assert.ok(await page.locator('#rrze-legal-dialog-title:focus').count());
    await page.keyboard.press('Escape');
    assert.ok(await modal.isVisible());
    await modal.getByRole('checkbox').check();
});

for (const mode of ['without JavaScript', 'showModal throws']) {
    test(`the acknowledgement form is reachable when running ${mode}`, async t => {
        const page = await openDashboard(t, mode);
        assert.equal(await page.locator('dialog:modal').count(), 0);
        assert.equal(await page.locator('#skip-link').isVisible(), false);
        assert.equal(await page.locator('#dashboard-action').isVisible(), false);
        assert.equal(await page.locator('dialog[open] form:visible').count(), 1);

        await page.route(confirmUrl, route => route.fulfill({ contentType: 'text/plain', body: 'Confirmed' }));
        await page.getByRole('checkbox').check();
        const submitted = page.waitForRequest(confirmUrl);
        await page.getByRole('button', { name: 'Confirm and return to the dashboard' }).click();
        const request = await submitted;
        assert.equal(request.method(), 'POST');
        assert.equal(request.postData(), 'confirm=1');
    });
}
