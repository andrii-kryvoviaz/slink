import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';

test.describe('Explore viewer modal', () => {
  let ownerFeedUrl: string;
  let imageIds: string[];

  test.beforeAll(async () => {
    const owner = await provisionUser(unique('viewer'));
    imageIds = [
      await owner.content.uploadImage({ isPublic: true }),
      await owner.content.uploadImage({ isPublic: true }),
      await owner.content.uploadImage({ isPublic: true }),
    ];
    const { displayName } = (await owner.users.getMe()).data;
    ownerFeedUrl = `/explore?search=${displayName}&searchBy=user`;
  });

  test.beforeEach(async ({ page }) => {
    await page.goto(ownerFeedUrl);
  });

  test('opens, navigates with buttons, and closes', async ({ explorePage }) => {
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

  test('navigates with down/up arrow keys, matching left/right, and closes with Escape', async ({
    explorePage,
  }) => {
    await explorePage.feedItems.first().waitFor({ state: 'visible' });

    await explorePage.openFirstItem();
    const postN = explorePage.currentPost();

    await explorePage.pressArrow('ArrowRight');
    const postN1 = explorePage.currentPost();
    expect(postN1).not.toBe(postN);

    await explorePage.pressArrow('ArrowDown');
    const postN2 = explorePage.currentPost();
    expect(postN2).not.toBe(postN1);
    expect(postN2).not.toBe(postN);

    await explorePage.pressArrow('ArrowLeft');
    await expect.poll(() => explorePage.currentPost()).toBe(postN1);

    await explorePage.pressArrow('ArrowUp');
    await expect.poll(() => explorePage.currentPost()).toBe(postN);

    await explorePage.page.keyboard.press('Escape');
    await expect(explorePage.viewer).toBeHidden();
  });

  test('opens the clicked image, not the first', async ({ explorePage }) => {
    const middleId = imageIds[1];
    await explorePage.cardFor(middleId).waitFor({ state: 'visible' });

    await explorePage.cardFor(middleId).click();
    await explorePage.viewer.waitFor({ state: 'visible' });

    await expect.poll(() => explorePage.currentPost()).toBe(middleId);
  });
});
