import {
  type Locator,
  type Page,
  type Response,
  expect,
} from '@playwright/test';

import { BasePage } from './BasePage';

export class CommentSection extends BasePage {
  readonly root: Locator;
  readonly heading: Locator;
  readonly input: Locator;
  readonly editInput: Locator;
  readonly menu: Locator;
  readonly deletedNotice: Locator;

  constructor(
    page: Page,
    private readonly imageId: string,
  ) {
    super(page);

    this.root = page.locator(`[data-post-id="${imageId}"]`);
    this.heading = this.root.getByRole('heading', { name: 'Comments' });
    this.input = this.root.getByPlaceholder('Write a comment...');
    this.editInput = this.root.getByPlaceholder('Edit your comment...');
    this.menu = page.getByRole('menu');
    this.deletedNotice = this.root.getByText('This comment has been deleted');
  }

  row(content: string) {
    return this.root
      .locator('[data-slot="scroll-area-viewport"] div.gap-3.p-2')
      .filter({ hasText: content });
  }

  actionsTrigger(content: string) {
    return this.row(content).getByLabel('Comment actions');
  }

  async waitForLoaded() {
    await expect(this.heading).toBeVisible();
    await expect(this.input).toBeVisible();
  }

  async post(content: string): Promise<Response> {
    const created = this.page.waitForResponse(
      (response) =>
        response.request().method() === 'POST' &&
        new URL(response.url()).pathname.endsWith(
          `/image/${this.imageId}/comments`,
        ),
    );

    await this.fillField(this.input, content);
    await this.input.press('Control+Enter');

    return created;
  }

  async edit(content: string, updated: string): Promise<Response> {
    await this.openActions(content);
    await this.menu.getByRole('menuitem', { name: 'Edit' }).click();
    await expect(this.editInput).toBeVisible();

    const saved = this.commentWrite('PATCH');

    await this.editInput.fill(updated);
    await this.editInput.press('Control+Enter');

    return saved;
  }

  async deleteComment(content: string): Promise<Response> {
    await this.openActions(content);
    await this.menu.getByRole('menuitem', { name: 'Delete' }).click();
    await expect(
      this.menu.getByRole('heading', { name: 'Delete Comment' }),
    ).toBeVisible();

    const removed = this.commentWrite('DELETE');

    await this.menu
      .getByRole('button', { name: 'Delete', exact: true })
      .click();

    return removed;
  }

  private async openActions(content: string) {
    await this.clickUntil(this.actionsTrigger(content), this.menu);
  }

  private commentWrite(method: 'PATCH' | 'DELETE'): Promise<Response> {
    return this.page.waitForResponse(
      (response) =>
        response.request().method() === method &&
        new URL(response.url()).pathname.includes('/comment/'),
    );
  }
}
