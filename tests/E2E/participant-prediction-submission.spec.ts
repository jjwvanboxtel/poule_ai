import { expect, test } from '@playwright/test';

test('participant can register, submit prediction, and review it read-only', async ({ page }) => {
  const uniqueEmail = `e2e+${Date.now()}@example.com`;

  await page.goto('/register');
  await page.getByLabel('Voornaam').fill('E2E');
  await page.getByLabel('Achternaam').fill('Tester');
  await page.getByLabel('E-mailadres').fill(uniqueEmail);
  await page.getByLabel('Telefoonnummer').fill('0612345678');
  await page.getByLabel('Wachtwoord').fill('veiligwachtwoord');
  await page.getByRole('button', { name: 'Account aanmaken' }).click();

  await expect(page).toHaveURL(/\/dashboard$/);
  await expect(page.getByText('EK 2026')).toBeVisible();
  await expect(page.getByText('Onbetaald')).toBeVisible();

  await page.getByRole('link', { name: /Voorspelling (bekijken|invullen)/ }).click();
  await expect(page).toHaveURL(/\/competitions\/ek-2026\/prediction$/);

  await page.getByLabel(/Thuisdoelpunten Nederland - Duitsland/).fill('2');
  await page.getByLabel(/Uitdoelpunten Nederland - Duitsland/).fill('1');
  await page.getByLabel(/Wedstrijdresultaat Nederland - Duitsland/).selectOption('home_win');
  await page.getByLabel(/Gele kaarten thuis Nederland - Duitsland/).fill('1');
  await page.getByLabel(/Gele kaarten uit Nederland - Duitsland/).fill('2');
  await page.getByLabel(/Rode kaarten thuis Nederland - Duitsland/).fill('0');
  await page.getByLabel(/Rode kaarten uit Nederland - Duitsland/).fill('0');

  await page.getByLabel(/Thuisdoelpunten Spanje - Frankrijk/).fill('1');
  await page.getByLabel(/Uitdoelpunten Spanje - Frankrijk/).fill('1');
  await page.getByLabel(/Wedstrijdresultaat Spanje - Frankrijk/).selectOption('draw');
  await page.getByLabel(/Gele kaarten thuis Spanje - Frankrijk/).fill('2');
  await page.getByLabel(/Gele kaarten uit Spanje - Frankrijk/).fill('2');
  await page.getByLabel(/Rode kaarten thuis Spanje - Frankrijk/).fill('0');
  await page.getByLabel(/Rode kaarten uit Spanje - Frankrijk/).fill('1');

  await page.getByLabel('Welk land wint het toernooi?').selectOption({ label: 'Nederland' });
  await page.getByLabel('Hoeveel goals vallen er in de finale?').fill('3');
  await page.getByLabel('Welke speler wordt topscorer?').fill('Memphis Depay');

  await page.getByLabel(/Finale · Positie 1/).selectOption({ label: 'Nederland' });
  await page.getByLabel(/Finale · Positie 2/).selectOption({ label: 'Spanje' });

  await page.getByRole('button', { name: 'Definitief indienen' }).click();

  await expect(page).toHaveURL(/confirmed=1/);
  await expect(page.getByText('Voorspelling bevestigd')).toBeVisible();
  await expect(page.getByText('Onbetaald')).toBeVisible();
  await expect(page.getByText('Status: Read-only')).toBeVisible();
  await expect(page.getByRole('button', { name: 'Definitief indienen' })).toHaveCount(0);
});
