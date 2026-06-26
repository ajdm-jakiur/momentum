import { chromium } from 'playwright';

(async () => {
    console.log('Starting Playwright...');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
        console.log('Navigating to login...');
        await page.goto('http://127.0.0.1:8080/login');
        
        console.log('Filling login form...');
        await page.fill('input[type="email"]', 'juliyansmith44@gmail.com');
        await page.fill('input[type="password"]', 'password');
        
        await Promise.all([
            page.waitForNavigation(),
            page.click('button[type="submit"]')
        ]);

        console.log('Navigating to the reader for book 1...');
        await page.goto('http://127.0.0.1:8080/books/1/read');
        
        console.log('Waiting for PDF to load (checking for canvas)...');
        await page.waitForSelector('canvas', { timeout: 60000 });
        
        console.log('Waiting a bit for render...');
        await page.waitForTimeout(5000);
        
        // Take a screenshot to verify
        await page.screenshot({ path: 'reader-initial.png' });
        console.log('Saved initial screenshot to reader-initial.png');

        // Let's get the current page from the UI
        const pageText = await page.evaluate(() => {
            return document.querySelector('div.font-mono.text-xs.text-white\\/60.text-center > span:nth-child(1)').innerText;
        });
        console.log('Current page before change:', pageText);

        console.log('Simulating right arrow to go to next page...');
        await page.keyboard.press('ArrowRight');
        
        console.log('Waiting for Livewire to save...');
        await page.waitForTimeout(4000); // Wait for the debounce (1.5s) and network request

        const newPageText = await page.evaluate(() => {
            return document.querySelector('div.font-mono.text-xs.text-white\\/60.text-center > span:nth-child(1)').innerText;
        });
        console.log('Current page after change:', newPageText);
        
        await page.screenshot({ path: 'reader-next-page.png' });
        console.log('Saved next page screenshot to reader-next-page.png');
        
        console.log('Reloading the page to test "pick up where you left off"...');
        await page.reload();
        
        console.log('Waiting for PDF to load again...');
        await page.waitForSelector('canvas', { timeout: 60000 });
        await page.waitForTimeout(5000);
        
        const reloadedPageText = await page.evaluate(() => {
            return document.querySelector('div.font-mono.text-xs.text-white\\/60.text-center > span:nth-child(1)').innerText;
        });
        console.log('Current page after reload:', reloadedPageText);

        if (reloadedPageText === newPageText) {
            console.log('SUCCESS: Progress was saved successfully!');
        } else {
            console.log('ERROR: Progress was not saved. Expected:', newPageText, 'Got:', reloadedPageText);
        }

    } catch (e) {
        console.error('Error during test:', e);
    } finally {
        await browser.close();
    }
})();
