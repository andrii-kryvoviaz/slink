import { expect, test } from '../fixtures/auth.fixture';

test.use({
  permissions: ['clipboard-read', 'clipboard-write'],
});

test.describe('Explore card copy link', () => {
  test('shows a copy link control for the signed-in owner without a bookmark control', async ({
    api,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    const card = explorePage.cardFor(imageId);
    await card.waitFor({ state: 'visible' });
    await card.hover();

    await expect(
      card.getByRole('button', { name: 'Copy link', exact: true }),
    ).toBeVisible();
    await expect(card.getByRole('button', { name: 'Save' })).toHaveCount(0);
  });

  test('shows a bookmark control without a copy link control for another owner image', async ({
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    const card = explorePage.cardFor(imageId);
    await card.waitFor({ state: 'visible' });
    await card.hover();

    await expect(card.getByRole('button', { name: 'Save' })).toBeVisible();
    await expect(
      card.getByRole('button', { name: 'Copy link', exact: true }),
    ).toHaveCount(0);
  });

  test('copies a share link from an owned card and publishes the share', async ({
    api,
    page,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    const card = explorePage.cardFor(imageId);
    await card.waitFor({ state: 'visible' });
    await card.hover();

    const copyButton = card.getByRole('button', {
      name: 'Copy link',
      exact: true,
    });
    await expect(copyButton).toBeVisible();

    const shared = page.waitForResponse(
      (response) =>
        response.request().method() === 'POST' &&
        response.url().includes(`/api/image/${imageId}/share`) &&
        response.ok(),
    );
    const published = page.waitForResponse(
      (response) =>
        response.request().method() === 'PUT' &&
        response.url().includes('/api/share/') &&
        response.url().endsWith('/publish') &&
        response.ok(),
    );

    await copyButton.click();
    await shared;
    await published;

    await expect(card.getByRole('button', { name: 'Copied' })).toBeVisible();

    const clipboardText = await page.evaluate(() =>
      navigator.clipboard.readText(),
    );
    expect(clipboardText.length).toBeGreaterThan(0);
  });
});
