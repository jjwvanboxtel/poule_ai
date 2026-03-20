import { expect, test } from '@playwright/test';

test('admin can view competitions and manage participants and bonus questions', async ({ page }) => {
  // Login as admin
  await page.goto('/login');
  await page.getByLabel('E-mailadres').fill('admin@example.com');
  await page.getByLabel('Wachtwoord').fill('secret');
  await page.getByRole('button', { name: 'Inloggen' }).click();

  // After login, navigate to admin dashboard
  await page.goto('/admin');
  await expect(page).toHaveURL(/\/admin$/);
  await expect(page.getByText('Admin Dashboard')).toBeVisible();

  // View competition list
  await page.goto('/admin/competitions');
  await expect(page).toHaveURL(/\/admin\/competitions$/);
  await expect(page.getByText('Competities')).toBeVisible();
  await expect(page.getByText('EK 2026')).toBeVisible();

  // Navigate to competition edit
  await page.getByRole('link', { name: /Bewerken|Edit/i }).first().click();
  await expect(page).toHaveURL(/\/admin\/competitions\/\d+\/edit$/);

  // Navigate to participants for this competition
  const competitionEditUrl = page.url();
  const competitionId = competitionEditUrl.match(/\/admin\/competitions\/(\d+)\/edit/)?.[1];
  expect(competitionId).toBeTruthy();

  await page.goto(`/admin/competitions/${competitionId}/participants`);
  await expect(page).toHaveURL(/\/participants$/);
  await expect(page.getByText('Deelnemers')).toBeVisible();

  // Mark participant as paid
  const markPaidButton = page.getByRole('button', { name: 'Markeer betaald' }).first();
  if (await markPaidButton.isVisible()) {
    await markPaidButton.click();
    await expect(page).toHaveURL(/\/participants$/);
    await expect(page.getByRole('table')).toBeVisible();
  }

  // Navigate to bonus questions
  await page.goto(`/admin/competitions/${competitionId}/bonus-questions`);
  await expect(page).toHaveURL(/\/bonus-questions$/);
  await expect(page.getByText('Bonusvragen')).toBeVisible();

  // Add a new bonus question
  await page.getByLabel(/Vraag/i).fill('Wie wordt de topscorer?');
  await page.getByLabel(/Type/i).selectOption('text');
  await page.getByRole('button', { name: /Opslaan|Toevoegen/i }).first().click();
  await expect(page).toHaveURL(/\/bonus-questions$/);
  await expect(page.getByText('Wie wordt de topscorer?')).toBeVisible();

  // Navigate to match management
  await page.goto(`/admin/competitions/${competitionId}/matches`);
  await expect(page).toHaveURL(/\/matches$/);
  await expect(page.getByText('Wedstrijden')).toBeVisible();

  // Navigate to entity import page
  await page.goto(`/admin/competitions/${competitionId}/import/entities`);
  await expect(page).toHaveURL(/\/import\/entities$/);
  await expect(page.getByText('Entiteiten importeren')).toBeVisible();

  // Navigate to maintenance
  await page.goto('/admin/maintenance');
  await expect(page).toHaveURL(/\/maintenance$/);
  await expect(page.getByText('Onderhoud')).toBeVisible();
});

test('unauthenticated user is redirected from admin routes', async ({ page }) => {
  await page.goto('/admin');
  await expect(page).toHaveURL(/\/login/);

  await page.goto('/admin/competitions');
  await expect(page).toHaveURL(/\/login/);

  await page.goto('/admin/maintenance');
  await expect(page).toHaveURL(/\/login/);
});

test('participant is forbidden from admin routes', async ({ page }) => {
  await page.goto('/login');
  await page.getByLabel('E-mailadres').fill('deelnemer@example.com');
  await page.getByLabel('Wachtwoord').fill('secret');
  await page.getByRole('button', { name: 'Inloggen' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);

  const adminResponse = await page.goto('/admin');
  expect(adminResponse?.status()).toBe(403);

  const competitionsResponse = await page.goto('/admin/competitions');
  expect(competitionsResponse?.status()).toBe(403);
});
