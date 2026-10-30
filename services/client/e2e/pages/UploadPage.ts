import { type Page, type Response, expect } from '@playwright/test';
import { readFileSync } from 'fs';
import { basename } from 'path';

import { isChunkedUploadCompletionResponse } from '../helpers/chunkedUpload';
import { createUniquePng } from '../helpers/uniqueImage';
import { BasePage } from './BasePage';

type UploadFile = { name: string; mimeType: string; buffer: Buffer };

export class UploadPage extends BasePage {
  static readonly URL = '/upload';

  readonly fileInput = this.page.locator('input[type="file"]');
  readonly dropzone = this.page.locator('[data-slot="dropzone-input"]');
  readonly publicVisibilityButton = this.page.getByRole('button', {
    name: 'Public',
  });
  readonly privateVisibilityButton = this.page.getByRole('button', {
    name: 'Private',
  });
  readonly visibilityToggle = this.page
    .getByRole('button', { name: /^(Public|Private)$/ })
    .first();
  readonly goToUploadsButton = this.page.getByRole('button', {
    name: 'Go to uploads',
  });
  readonly cancelButton = this.page.getByRole('button', { name: 'Cancel' });
  readonly retryFailedButton = this.page.getByRole('button', {
    name: 'Retry Failed',
  });
  readonly uploadMoreButton = this.page.getByRole('button', {
    name: 'Upload more',
  });

  readonly collectionBanner = this.page
    .locator('div.rounded-xl')
    .filter({
      hasText: /Combine into a collection|Collection created|Ready to share/,
    })
    .last();
  readonly collectionNameInput = this.page.getByPlaceholder('Unnamed');
  readonly createPublicationButton = this.page.getByRole('button', {
    name: 'Create publication',
  });
  readonly createCollectionButton = this.page.getByRole('button', {
    name: 'Create collection',
  });
  readonly viewCollectionButton = this.page.getByRole('button', {
    name: 'View collection',
  });
  readonly collectionCreatedHeading = this.page.getByText('Collection created');
  readonly collectionsOption = this.page.getByRole('button', {
    name: 'Collections',
    exact: true,
  });
  readonly collectionsPicker = this.page
    .locator('[data-slot="popover-content"]')
    .filter({ has: this.page.getByRole('listbox') });
  readonly collectionsSearchInput =
    this.collectionsPicker.getByPlaceholder('Search');
  readonly tagsOption = this.page.getByRole('button', {
    name: 'Tags',
    exact: true,
  });
  readonly tagsPicker = this.collectionsPicker;
  readonly tagsSearchInput = this.tagsPicker.getByPlaceholder('Search');
  readonly uploadAnotherButton = this.page.getByRole('button', {
    name: 'Upload Another Image',
  });
  readonly createAccountLink = this.page.getByRole('link', {
    name: 'Create Account',
  });
  readonly viewHistoryAction = this.page.getByText('View History');
  readonly guestNoticeHeading = this.page.getByRole('heading', {
    name: 'Guest Upload Notice',
  });
  readonly guestNoticeBody = this.page.getByText(
    'consider creating an account',
  );

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: /Drop your image/ });
  }

  get successHeading() {
    return this.page.getByRole('heading', { name: 'Upload Successful!' });
  }

  get multiUploadHeading() {
    return this.page.getByRole('heading', { name: /^(Uploading|Complete)$/ });
  }

  async goto() {
    await this.page.goto(UploadPage.URL);
  }

  async uploadNonImageFile() {
    await this.chooseFiles({
      name: `e2e-${Date.now()}.txt`,
      mimeType: 'text/plain',
      buffer: Buffer.from('not an image'),
    });
  }

  async uploadMultipleImages(count: number) {
    const files = Array.from({ length: count }, () => {
      const { buffer, name } = createUniquePng();
      return { name, mimeType: 'image/png', buffer };
    });

    await this.uploadExpectingCompletion(files);
  }

  async uploadFiles(paths: string[]) {
    await this.fileInput.setInputFiles(paths);
  }

  async uploadFixtureFile(path: string, mimeType: string): Promise<Response> {
    const name = basename(path);
    const buffer = readFileSync(path);

    return this.uploadBuffer({ name, mimeType, buffer });
  }

  async uploadBuffer(file: UploadFile): Promise<Response> {
    return this.uploadExpectingCompletion(file, 30000);
  }

  async uploadSingleImage() {
    const { buffer, name } = createUniquePng();

    await this.uploadFilesExpectingRequest([
      { name, mimeType: 'image/png', buffer },
    ]);
  }

  async uploadFilesExpectingRequest(files: UploadFile[]) {
    await Promise.all([
      this.page.waitForRequest(
        (request) =>
          /\/api\/upload\/chunked$/.test(request.url()) &&
          request.method() === 'POST',
        { timeout: 15000 },
      ),
      this.chooseFiles(files),
    ]);
  }

  async uploadUniqueImage() {
    const { buffer, name } = createUniquePng();

    await this.uploadExpectingCompletion({
      name,
      mimeType: 'image/png',
      buffer,
    });
  }

  private async uploadExpectingCompletion(
    files: UploadFile | UploadFile[],
    timeout = 15000,
  ): Promise<Response> {
    const [completion] = await Promise.all([
      this.page.waitForResponse(isChunkedUploadCompletionResponse, {
        timeout,
      }),
      this.chooseFiles(files),
    ]);

    return completion;
  }

  private async chooseFiles(files: UploadFile | UploadFile[]) {
    await expect(async () => {
      const [chooser] = await Promise.all([
        this.page.waitForEvent('filechooser', { timeout: 500 }),
        this.dropzone.press('Space', { timeout: 500 }),
      ]);
      await chooser.setFiles(files);
    }).toPass({ timeout: 15000, intervals: [100] });
  }

  async setVisibilityPublic() {
    await this.visibilityToggle.waitFor({ state: 'visible' });
    const label = await this.visibilityToggle.textContent();
    if (label && label.trim().startsWith('Private')) {
      await this.visibilityToggle.click();
    }
  }

  async waitForSuccess() {
    await this.successHeading.waitFor({ state: 'visible', timeout: 30000 });
  }

  async waitForUploadComplete() {
    await this.page.waitForURL(/\/info\//, { timeout: 30000 });
  }

  collectionsPickerItem(name: string) {
    return this.collectionsPicker.getByRole('button', { name, exact: true });
  }

  tagsPickerItem(name: string) {
    return this.tagsPicker.getByRole('button', {
      name: new RegExp(`^${name}( |$)`),
    });
  }

  createRow(name: string) {
    return this.collectionsPicker.getByRole('button', {
      name: `Create "${name}"`,
    });
  }

  async searchTags(term: string) {
    await this.clickUntil(this.tagsOption, this.tagsSearchInput);
    await this.tagsSearchInput.fill(term);
  }

  async searchCollections(term: string) {
    await this.clickUntil(this.collectionsOption, this.collectionsSearchInput);
    await this.collectionsSearchInput.fill(term);
  }
}
