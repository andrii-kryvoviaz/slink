import { randomUUID } from 'node:crypto';

import { expect, test } from '../fixtures/auth.fixture';

const REPEATS = 10;

const VISIBILITIES = [
  { label: 'private', isPublic: false },
  { label: 'public', isPublic: true },
];

test.describe('Image info page access', () => {
  for (const { label, isPublic } of VISIBILITIES) {
    test(`answers a non-owner opening a ${label} image with the 403 page every time`, async ({
      actor,
      page,
      imageInfoPage,
    }) => {
      const owner = await actor('owner');
      const imageId = await owner.content.uploadImage({ isPublic });

      for (let attempt = 1; attempt <= REPEATS; attempt++) {
        await imageInfoPage.goto(imageId);

        await expect(
          page.getByRole('heading', { name: '403', exact: true }),
          `attempt ${attempt}`,
        ).toBeVisible();
      }
    });
  }

  test('keeps the tags and the page of an image for its owner', async ({
    api,
    imageInfoPage,
  }) => {
    const tagName = `owned-${Date.now()}`;
    const imageId = await api.content.uploadImage({ isPublic: false });
    const tagId = await api.content.createTag(tagName);
    await api.content.tagImage(imageId, tagId);

    await imageInfoPage.goto(imageId);

    await expect(imageInfoPage.tagLink(tagName)).toBeVisible();
    await expect(imageInfoPage.visibilityButton).toBeVisible();
  });

  test('sends a user opening an unknown image to the history page', async ({
    page,
    imageInfoPage,
  }) => {
    await imageInfoPage.goto(randomUUID());

    await page.waitForURL('**/history');
  });
});
