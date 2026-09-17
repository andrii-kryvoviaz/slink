import { expect, test } from '../fixtures/auth.fixture';

test.describe('Explore view mode', () => {
  test('switching to list renders rows, survives reload, and switches back', async ({
    page,
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await expect(explorePage.cardFor(imageId)).toBeVisible();

    await explorePage.switchViewMode('List');

    await expect(
      explorePage.listRows.filter({
        has: page.locator(`img[src*="${imageId}"]`),
      }),
    ).toHaveCount(1);

    await page.reload();
    await expect(explorePage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(explorePage.listRows.first()).toBeVisible();

    await explorePage.switchViewMode('Grid');
    await expect(explorePage.listRows).toHaveCount(0);
    await expect(explorePage.cardFor(imageId)).toBeVisible();
  });

  test("list rows load thumbnails for another user's public images", async ({
    page,
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });

    await explorePage.goto();
    await expect(explorePage.cardFor(imageId)).toBeVisible();

    await explorePage.switchViewMode('List');

    const row = explorePage.listRows.filter({
      has: page.locator(`img[src*="${imageId}"]`),
    });
    const thumbnail = row.locator(`img[src*="${imageId}"]`);

    await expect(row).toHaveCount(1);
    await expect
      .poll(() =>
        thumbnail.evaluate(
          (img: HTMLImageElement) => img.complete && img.naturalWidth > 0,
        ),
      )
      .toBe(true);
    await expect(row.getByText('Image unavailable')).toHaveCount(0);
  });

  test('a list cookie paints rows without a click and a row opens the viewer', async ({
    page,
    explorePage,
    layoutControls,
    actor,
  }) => {
    const owner = await actor('owner');
    await owner.content.uploadImage({ isPublic: true });

    await layoutControls.setSettingCookie(
      'explore',
      JSON.stringify({ viewMode: 'list' }),
    );

    await explorePage.goto();
    await expect(explorePage.viewModeOption('List')).toHaveAttribute(
      'aria-checked',
      'true',
    );
    await expect(explorePage.listRows.first()).toBeVisible();

    await explorePage.clickUntil(
      explorePage.listRows.first(),
      explorePage.viewer,
    );
    expect(explorePage.currentPost()).toBeTruthy();
  });
});
