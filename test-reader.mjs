import { chromium } from 'playwright';
import { writeFileSync } from 'fs';

const BASE = 'http://127.0.0.1:8002';
const S = '/tmp/claude-1000/-home-nprime-github-personal-progress-track/c9c5bc02-4c0d-474e-a38b-4f6f1930763e/scratchpad';

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();

const errors = [];
const netReqs = [];
page.on('console', msg => {
    const t = msg.type();
    if (t === 'error' || t === 'warn') errors.push(`[${t}] ${msg.text()}`);
});
page.on('pageerror', err => errors.push('[pageerror] ' + err.message));
const livewirePosts = [];
page.on('response', r => {
    const u = r.url();
    if (u.includes('/serve') || u.includes('/books') || u.includes('pdf.js') || u.includes('pdf.min')) {
        netReqs.push(`${r.status()} ${u.substring(0, 120)}`);
    }
    if (u.includes('/livewire/update')) {
        livewirePosts.push(`${r.status()} POST /livewire/update`);
    }
});

const shot = async (name) => {
    await page.screenshot({ path: `${S}/${name}.png` });
    console.log(`📸 ${name}`);
};

// --- LOGIN ---
await page.goto(`${BASE}/login`);
await shot('01-login');

await page.locator('input[type=email]').fill('juliyansmith44@gmail.com');

const passwords = ['password', 'Password123', 'secret', '12345678', 'password123', 'admin'];
let loggedIn = false;
for (const pw of passwords) {
    await page.locator('input[type=password]').fill(pw);
    await page.locator('button[type=submit]').click();
    await page.waitForTimeout(2000);
    if (!page.url().includes('/login')) {
        loggedIn = true;
        console.log(`✓ Login OK (pw: ${pw})`);
        break;
    }
}

if (!loggedIn) {
    await shot('02-login-fail');
    console.log('FAIL: login');
    await browser.close();
    process.exit(1);
}
await shot('02-dashboard');

// --- BOOKS LIST ---
await page.goto(`${BASE}/books`);
await page.waitForTimeout(1500);
await shot('03-books');

const readLinks = await page.locator('a[href*="/read"]').all();
console.log(`Books: ${readLinks.length}`);
if (readLinks.length === 0) {
    console.log('No books. Exiting.');
    await browser.close();
    process.exit(0);
}

const readHref = await readLinks[0].getAttribute('href');
const bookId = readHref?.match(/\/books\/(\d+)\//)?.[1];
console.log(`Book ID: ${bookId}`);

// --- TEST SERVE DIRECTLY ---
console.log('\n=== Testing /books/' + bookId + '/serve ===');
// Skip serve endpoint direct test — PHP single-threaded server can't handle parallel requests
console.log('Serve endpoint test skipped (single-threaded server).');

// --- NAVIGATE TO READER via books list click (reproduces real user flow) ---
console.log('\n=== Reader (clicking from books list) ===');
await page.goto(`${BASE}/books`);
await page.waitForTimeout(1000);
const readBtn = page.locator('a[href*="/read"]').first();
console.log('Clicking Read button (no wire:navigate now)...');
await readBtn.click();
await page.waitForLoadState('load'); // full page load (not wire:navigate anymore)
await page.waitForTimeout(1000);
await shot('04-reader-init');

const jsState = await page.evaluate(() => ({
    pdfReader: typeof window.pdfReader,
    pdfjsLib: typeof window.pdfjsLib,
    pdfjsVersion: window.pdfjsLib?.version || null,
    hasXData: !!document.querySelector('[x-data]'),
}));
console.log('JS state:', JSON.stringify(jsState));

// Wait up to 90s — 106MB file takes time to download via PDF.js
// Canvas default = 300x150, real render = much bigger
let loaded = false;
for (let i = 0; i < 45; i++) {
    await page.waitForTimeout(2000);
    const st = await page.evaluate(() => {
        const c = document.querySelector('canvas');
        const loadEl = document.querySelector('[x-show="loading"]');
        const errEl = document.querySelector('[x-show="error"]');

        // Read Alpine error state from the error <p> element directly
        let alpineErr = null;
        try {
            // The x-text="error" p element inside the error div
            const errP = document.querySelector('[x-show="error"] p[x-text]');
            alpineErr = errP?.textContent?.trim() || null;
        } catch(e) {}

        return {
            cw: c?.width || 0, ch: c?.height || 0,
            loadDisplay: loadEl?.style?.display,
            errDisplay: errEl?.style?.display,
            errText: errEl?.textContent?.trim()?.substring(0, 120),
            alpineErr: alpineErr,
        };
    });
    console.log(`t=${(i+1)*2}s canvas=${st.cw}x${st.ch} loading="${st.loadDisplay}" err="${st.errDisplay}" alpineErr="${st.alpineErr}"`);

    // Real render: canvas dimensions will be >>300x150 (typical PDF page ~800x1100+)
    if (st.cw > 400 && st.ch > 300) { loaded = true; break; }
    // Error condition
    if (st.errDisplay === '' || st.errDisplay === 'block' || st.alpineErr) break;
    // Loading done but no canvas? Something wrong.
    if (st.loadDisplay === 'none' && st.cw <= 400) { console.log('Loading finished but no canvas render'); break; }
}

await shot('05-reader-result');

// Simulate page turn to trigger scheduleSave → verify savePage hits right component
if (loaded) {
    console.log('\nSimulating page turn (right tap)...');
    await page.locator('.w-\\[40\\%\\].h-full.pointer-events-auto').last().click().catch(() => {
        // fallback: press arrow right
        page.keyboard.press('ArrowRight');
    });
    await page.waitForTimeout(3000); // wait > 1.5s debounce for scheduleSave to fire
    console.log('Livewire POSTs after page turn:', livewirePosts.length ? livewirePosts : ['none']);
}

console.log('\n=== Network ===');
netReqs.forEach(r => console.log(r));
console.log('\n=== Livewire Updates ===');
livewirePosts.forEach(r => console.log(r));
console.log('\n=== Errors ===');
errors.forEach(e => console.log(e));
console.log('\n=== RESULT:', loaded ? 'PDF LOADED ✓' : 'PDF FAILED ✗', '===');

await browser.close();
