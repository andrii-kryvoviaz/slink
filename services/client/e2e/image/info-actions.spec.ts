import { expect, test } from '../fixtures/auth.fixture';

test.describe('Image info page actions', () => {
  test('toggles visibility from private to public', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await imageInfoPage.goto(imageId);

    const button = imageInfoPage.visibilityButton;
    await expect(button).toBeVisible();
    await expect(button).toHaveAttribute('aria-label', 'Make public');
    await expect(button).toHaveAttribute('aria-pressed', 'false');

    const patchResponse = page.waitForResponse(
      (response) =>
        response.url().includes(`/api/image/${imageId}`) &&
        response.request().method() === 'PATCH',
    );

    await imageInfoPage.toggleVisibility();

    const response = await patchResponse;
    expect(response.ok()).toBe(true);

    await expect(button).toHaveAttribute('aria-label', 'Make private');
    await expect(button).toHaveAttribute('aria-pressed', 'true');
  });

  test('saves a description and persists it across reload', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });
    const description = `Info description ${Date.now()}`;

    await imageInfoPage.goto(imageId);

    const patchResponse = page.waitForResponse(
      (response) =>
        response.url().includes(`/api/image/${imageId}`) &&
        response.request().method() === 'PATCH',
    );

    await imageInfoPage.saveDescription(description);
    await patchResponse;

    await page.reload();

    await expect(page.getByText(description)).toBeVisible();
  });

  test('adds the image to a collection', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    const collectionName = `Info Collection ${Date.now()}`;
    await api.content.createCollection({ name: collectionName });
    const imageId = await api.content.uploadImage({ isPublic: false });

    await imageInfoPage.goto(imageId);

    const addResponse = page.waitForResponse(
      (response) =>
        response.url().includes('/api/collection/') &&
        response.url().includes(`/items/${imageId}`) &&
        response.request().method() === 'POST',
    );

    await imageInfoPage.addToCollection(collectionName);
    await addResponse;

    const collectionList = page
      .locator('a')
      .filter({ hasText: collectionName });
    await expect(collectionList.first()).toBeVisible();
  });

  test('deletes the image and redirects away from the info page', async ({
    api,
    page,
    imageInfoPage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: false });

    await imageInfoPage.goto(imageId);
    await expect(page).toHaveURL(new RegExp(`/info/${imageId}`));

    await imageInfoPage.deleteImage();

    await expect(page).not.toHaveURL(/\/info\//);
    await expect(page).toHaveURL(/\/history/);
  });
});
