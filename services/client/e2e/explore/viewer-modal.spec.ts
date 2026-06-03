import { expect, test } from '../fixtures/auth.fixture';

test.use({ storageState: 'e2e/.auth/user.json' });

test.describe('Explore viewer modal', () => {
  test.beforeEach(async ({ api }) => {
    await api.content.uploadImage({ isPublic: true });
    await api.content.uploadImage({ isPublic: true });
    await api.content.uploadImage({ isPublic: true });
  });

  test('opens, navigates with buttons, and closes', async ({ explorePage }) => {
    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.openFirstItem();
    await expect(explorePage.viewer).toBeVisible();

    const initialPost = new URL(explorePage.page.url()).searchParams.get(
      'post',
    );
    expect(initialPost).not.toBeNull();

    await explorePage.nextItem();
    await expect
      .poll(() => new URL(explorePage.page.url()).searchParams.get('post'))
      .not.toBe(initialPost);

    const nextPost = new URL(explorePage.page.url()).searchParams.get('post');

    await explorePage.prevItem();
    await expect
      .poll(() => new URL(explorePage.page.url()).searchParams.get('post'))
      .not.toBe(nextPost);

    await explorePage.closeViewer();
    await expect(explorePage.viewer).toBeHidden();
  });

  test('navigates with arrow keys', async ({ explorePage }) => {
    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.openFirstItem();

    const initialPost = new URL(explorePage.page.url()).searchParams.get(
      'post',
    );

    await explorePage.pressArrow('ArrowRight');
    await expect
      .poll(() => new URL(explorePage.page.url()).searchParams.get('post'))
      .not.toBe(initialPost);

    const afterNext = new URL(explorePage.page.url()).searchParams.get('post');

    await explorePage.pressArrow('ArrowLeft');
    await expect
      .poll(() => new URL(explorePage.page.url()).searchParams.get('post'))
      .not.toBe(afterNext);
  });

  test('closes with Escape key', async ({ explorePage }) => {
    await explorePage.goto();
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.openFirstItem();
    await expect(explorePage.viewer).toBeVisible();

    await explorePage.page.keyboard.press('Escape');
    await expect(explorePage.viewer).toBeHidden();
  });
});
