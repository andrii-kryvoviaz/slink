import { expect, test } from '../fixtures/auth.fixture';
import { CommentSection } from '../pages/CommentSection';

test.describe('Comment CRUD', () => {
  test('owner posts a comment from the form and it survives a reload', async ({
    api,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const content = `Owner comment ${Date.now()}`;

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const comments = new CommentSection(explorePage.page, imageId);
    await comments.waitForLoaded();

    const created = await comments.post(content);
    expect(created.ok()).toBe(true);

    await expect(comments.row(content)).toBeVisible();
    await expect(comments.input).toHaveValue('');

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await comments.waitForLoaded();

    await expect(comments.row(content)).toBeVisible();
  });

  test('owner edits their own comment and the new text persists', async ({
    api,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const stamp = Date.now();
    const original = `Original comment ${stamp}`;
    const updated = `Rewritten comment ${stamp}`;

    await api.content.createComment(imageId, original);

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const comments = new CommentSection(explorePage.page, imageId);
    await comments.waitForLoaded();
    await expect(comments.row(original)).toBeVisible();

    const saved = await comments.edit(original, updated);
    expect(saved.ok()).toBe(true);

    await expect(comments.row(updated)).toBeVisible();
    await expect(comments.root.getByText(original)).toHaveCount(0);

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await comments.waitForLoaded();

    await expect(comments.row(updated)).toBeVisible();
    await expect(comments.row(updated).getByText('(edited)')).toBeVisible();
    await expect(comments.root.getByText(original)).toHaveCount(0);
  });

  test('owner deletes their own comment through the confirmation dialog', async ({
    api,
    explorePage,
  }) => {
    const imageId = await api.content.uploadImage({ isPublic: true });
    const content = `Doomed comment ${Date.now()}`;

    await api.content.createComment(imageId, content);

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const comments = new CommentSection(explorePage.page, imageId);
    await comments.waitForLoaded();
    await expect(comments.row(content)).toBeVisible();

    const removed = await comments.deleteComment(content);
    expect(removed.ok()).toBe(true);

    await expect(comments.root.getByText(content)).toHaveCount(0);
    await expect(comments.deletedNotice).toBeVisible();

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await comments.waitForLoaded();

    await expect(comments.root.getByText(content)).toHaveCount(0);
  });

  test('non-owner sees the comment without edit or delete controls and can still post', async ({
    explorePage,
    actor,
  }) => {
    const owner = await actor('owner');
    const imageId = await owner.content.uploadImage({ isPublic: true });
    const stamp = Date.now();
    const ownerComment = `Owner comment ${stamp}`;
    const nonOwnerComment = `Non owner comment ${stamp}`;

    await owner.content.createComment(imageId, ownerComment);

    await explorePage.page.goto(`/explore?post=${imageId}`);
    await expect(explorePage.viewer).toBeVisible();

    const comments = new CommentSection(explorePage.page, imageId);
    await comments.waitForLoaded();

    await expect(comments.row(ownerComment)).toBeVisible();
    await expect(comments.actionsTrigger(ownerComment)).toHaveCount(0);

    const created = await comments.post(nonOwnerComment);
    expect(created.ok()).toBe(true);

    await expect(comments.row(nonOwnerComment)).toBeVisible();
    await expect(comments.actionsTrigger(nonOwnerComment)).toBeVisible();
  });
});
