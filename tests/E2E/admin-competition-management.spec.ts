import { test, expect } from '@playwright/test';

const BASE_URL = process.env.APP_URL ?? 'http://localhost:8000';

async function loginAsAdmin(page: any): Promise<void> {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await page.click('button[type="submit"]');
    await page.waitForURL(`${BASE_URL}/dashboard`);
}

test.describe('Admin Competition Management', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('admin can view competition list', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions`);
        await expect(page.locator('h1, h2, h3')).toContainText('Competities');
        await expect(page.locator('body')).toContainText('EK 2026');
    });

    test('admin can navigate to create competition form', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions`);
        await page.click('a[href="/admin/competitions/create"]');
        await expect(page.locator('h1, h2, h3, h4, h5')).toContainText('Nieuwe competitie');
        await expect(page.locator('form')).toBeVisible();
    });

    test('admin can create a new competition', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions/create`);

        await page.fill('input[name="name"]', `Test Competitie E2E ${Date.now()}`);
        await page.fill('textarea[name="description"]', 'E2E test competition');
        await page.fill('input[name="start_date"]', '2027-06-01');
        await page.fill('input[name="end_date"]', '2027-06-30');
        await page.fill('input[name="submission_deadline"]', '2027-05-31T23:59');
        await page.fill('input[name="entry_fee_amount"]', '10.00');
        await page.fill('input[name="prize_first_percent"]', '60');
        await page.fill('input[name="prize_second_percent"]', '30');
        await page.fill('input[name="prize_third_percent"]', '10');

        await page.click('button[type="submit"]');

        await expect(page).toHaveURL(`${BASE_URL}/admin/competitions`);
    });

    test('admin sees validation error for invalid prize distribution', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions/create`);

        await page.fill('input[name="name"]', 'Invalid Prize Competition');
        await page.fill('input[name="start_date"]', '2027-06-01');
        await page.fill('input[name="end_date"]', '2027-06-30');
        await page.fill('input[name="submission_deadline"]', '2027-05-31T23:59');
        await page.fill('input[name="prize_first_percent"]', '70');
        await page.fill('input[name="prize_second_percent"]', '30');
        await page.fill('input[name="prize_third_percent"]', '10'); // total = 110

        await page.click('button[type="submit"]');

        // Should be redirected back with an error
        await expect(page.locator('.alert-danger, .alert')).toBeVisible();
    });
});

test.describe('Admin Match Management', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('admin can view matches for a competition', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions`);

        // Find the EK 2026 competition and navigate to matches
        await page.click('a[href*="/matches"]');
        await expect(page.locator('h1, h2, h3, h4')).toContainText('Wedstrijden');
    });
});

test.describe('Admin Maintenance', () => {
    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('admin can view maintenance page', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/maintenance`);
        await expect(page.locator('h1, h2, h3')).toContainText('Onderhoud');
        await expect(page.locator('body')).toContainText('Migraties');
    });
});

test.describe('Admin Access Control', () => {
    test('participant is denied access to admin pages', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'deelnemer@example.com');
        await page.fill('input[name="password"]', 'secret');
        await page.click('button[type="submit"]');
        await page.waitForURL(`${BASE_URL}/dashboard`);

        const response = await page.goto(`${BASE_URL}/admin/competitions`);
        expect(response?.status()).toBe(403);
    });

    test('unauthenticated user is redirected to login', async ({ page }) => {
        await page.goto(`${BASE_URL}/admin/competitions`);
        await expect(page).toHaveURL(new RegExp('/login'));
    });
});
