import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export class HistoryPage extends BasePage {
  static readonly URL = '/history';

  readonly gridCards = this.page
    .locator('article')
    .filter({ has: this.page.locator('a[href^="/info/"]') });
  readonly selectCheckboxes = this.page.getByRole('button', {
    name: 'Select image',
  });
  readonly deselectCheckboxes = this.page.getByRole('button', {
    name: 'Deselect image',
  });
  readonly actionBar = this.page
    .locator('div')
    .filter({
      has: this.page.getByRole('button', {
        name: /^(Select all|Deselect all)$/,
      }),
    })
    .last();

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Upload History' });
  }

  get loadMoreButton() {
    return this.page.getByRole('button', { name: 'View More' });
  }

  get deleteButton() {
    return this.actionBar
      .getByRole('button', { name: 'Delete', exact: true })
      .first();
  }

  async goto() {
    await this.page.goto(HistoryPage.URL);
  }

  async useGridView() {
    await this.useView('grid');
  }

  async useTableView() {
    await this.useView('table');
  }

  private async useView(viewMode: 'grid' | 'list' | 'table') {
    await this.page.context().addCookies([
      {
        name: 'settings.history',
        value: JSON.stringify({ viewMode }),
        url: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
      },
    ]);
  }

  async selectImages(count: number) {
    await this.gridCards.first().waitFor({ state: 'visible' });
    for (let index = 0; index < count; index++) {
      const card = this.gridCards.nth(index);
      const checkbox = card
        .getByRole('button', {
          name: /^(Select image|Deselect image)$/,
        })
        .first();

      await expect(async () => {
        await checkbox.click();
        await expect(checkbox).toHaveAttribute('aria-label', 'Deselect image', {
          timeout: 1000,
        });
      }).toPass({ timeout: 15000 });
    }
  }

  confirmDeleteButton(count: number) {
    const label = count === 1 ? 'Delete Image' : 'Delete Images';
    return this.page.getByRole('button', { name: label });
  }

  async batchDelete(count: number) {
    await this.deleteButton.click();
    const confirm = this.confirmDeleteButton(count);
    await confirm.waitFor({ state: 'visible' });
    await confirm.click();
  }

  async visibleImageIds(): Promise<string[]> {
    const hrefs = await this.gridCards
      .locator('a[href^="/info/"]')
      .evaluateAll((links) =>
        links.map((link) => link.getAttribute('href') ?? ''),
      );

    return [...new Set(hrefs.map((href) => href.replace('/info/', '')))];
  }

  infoLink(id: string) {
    return this.page.locator(`a[href="/info/${id}"]`).first();
  }

  cardFor(id: string) {
    return this.infoLink(id).locator('xpath=ancestor::article[1]');
  }

  async deleteSingle(id: string) {
    const card = this.cardFor(id);
    await card.waitFor({ state: 'visible' });

    const confirm = this.page
      .getByRole('button', { name: 'Delete Image', exact: true })
      .last();

    await expect(async () => {
      await this.openDeleteMenu(card);
      await expect(confirm).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await confirm.click();
  }

  private async openDeleteMenu(card: Locator) {
    await card.hover();

    const directDelete = card.locator(
      'button[aria-label="Delete image"]:visible',
    );
    if ((await directDelete.count()) > 0) {
      await directDelete.first().click();
      return;
    }

    await card
      .locator('button[aria-label="Image actions"]:visible')
      .first()
      .click();
    await this.page
      .getByRole('menuitem', { name: 'Delete image' })
      .first()
      .click();
  }

  async copyImageContent(id: string) {
    const card = this.cardFor(id);
    await card.waitFor({ state: 'visible' });

    const caret = card
      .locator('button[aria-label="Copy link options"]:visible')
      .first();
    const formatItem = this.page
      .getByRole('menuitem', { name: 'Image Content' })
      .filter({ visible: true })
      .first();

    await expect(async () => {
      await card.hover();
      await caret.click();
      await expect(formatItem).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await formatItem.click();
  }

  async clipboardImageTypes(): Promise<string[]> {
    return this.page.evaluate(async () => {
      const items = await navigator.clipboard.read();
      return items.flatMap((item) => [...item.types]);
    });
  }

  get tableRows() {
    return this.page.getByRole('table').locator('tbody tr');
  }

  tableRowFor(id: string) {
    return this.tableRows.filter({
      has: this.page.locator(`a[href="/info/${id}"]`),
    });
  }

  async visibleTableImageIds(): Promise<string[]> {
    const hrefs = await this.tableRows.evaluateAll((rows) =>
      rows.map(
        (row) =>
          row.querySelector('a[href^="/info/"]')?.getAttribute('href') ?? '',
      ),
    );

    return hrefs.map((href) => href.replace('/info/', ''));
  }

  async deleteTableRow(id: string) {
    const row = this.tableRowFor(id);
    await row.waitFor({ state: 'visible' });

    const confirm = this.page
      .getByRole('button', { name: 'Delete Image', exact: true })
      .last();

    await expect(async () => {
      await this.openDeleteMenu(row);
      await expect(confirm).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await confirm.click();
  }

  viewModeOption(name: 'Grid' | 'List' | 'Table') {
    return this.page.getByRole('radio', { name });
  }

  async switchViewMode(name: 'Grid' | 'List' | 'Table') {
    const option = this.viewModeOption(name);
    await expect(async () => {
      await option.click();
      await expect(option).toHaveAttribute('aria-checked', 'true', {
        timeout: 1000,
      });
    }).toPass({ timeout: 15000 });
  }

  get nextTablePageButton() {
    return this.page.locator('button[title="Next page"]');
  }

  get prevTablePageButton() {
    return this.page.locator('button[title="Previous page"]');
  }

  get currentTablePageButton() {
    return this.page.locator('button[aria-label^="Current page "]');
  }

  async tableCurrentPageNumber(): Promise<number> {
    const label = await this.currentTablePageButton.getAttribute('aria-label');
    return Number((label ?? '').replace('Current page ', '').trim());
  }

  async goToNextTablePage() {
    const [firstBefore] = await this.visibleTableImageIds();
    await this.nextTablePageButton.click();
    await expect
      .poll(async () => (await this.visibleTableImageIds())[0], {
        timeout: 15000,
      })
      .not.toBe(firstBefore);
  }

  async selectTableRows(ids: string[]) {
    for (const id of ids) {
      await this.tableRowFor(id).getByRole('checkbox').click();
    }
    await expect(
      this.actionBar.getByText(`${ids.length} selected`, { exact: true }),
    ).toBeVisible();
  }

  private pickerOverlay(title: string) {
    return this.page
      .locator('[data-slot="popover-content"]')
      .filter({ hasText: title });
  }

  private popoverTrigger(name: string) {
    return this.actionBar
      .locator('[data-slot="popover-trigger"]')
      .filter({ hasText: name });
  }

  private async applyPicker(
    triggerName: string,
    overlayTitle: string,
    itemName: string,
  ) {
    await this.popoverTrigger(triggerName).click();

    const overlay = this.pickerOverlay(overlayTitle);
    await expect(overlay).toBeVisible();

    const item = overlay.getByRole('button', { name: itemName });
    const apply = overlay.getByRole('button', { name: 'Apply' });

    await expect(async () => {
      await item.click();
      await expect(apply).toBeEnabled({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await apply.click();
    await expect(overlay).toBeHidden({ timeout: 15000 });
  }

  async reassignCollection(name: string) {
    await this.applyPicker('Collection', 'Add to Collection', name);
  }

  async reassignTag(name: string) {
    await this.applyPicker('Tag', 'Assign Tags', name);
  }

  async setVisibility(target: 'public' | 'private') {
    await this.popoverTrigger('Visibility').click();

    const overlay = this.pickerOverlay('Change Visibility');
    await expect(overlay).toBeVisible();

    const label = target === 'public' ? 'Make Public' : 'Make Private';
    await overlay.getByRole('button', { name: label }).click();
    await expect(overlay).toBeHidden({ timeout: 15000 });
  }

  collectionBadgeIn(scope: Locator, collectionId: string) {
    return scope.locator(`a[href="/collection/${collectionId}"]`);
  }

  tagBadgeIn(scope: Locator, tagName: string) {
    return scope.locator('a[href*="tagIds="]').filter({ hasText: tagName });
  }
}
