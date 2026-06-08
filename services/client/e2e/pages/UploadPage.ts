import { type Page, type Response, expect } from '@playwright/test';
import { readFileSync } from 'fs';
import { basename } from 'path';

import { isChunkedUploadCompletionResponse } from '../helpers/chunkedUpload';
import { createUniquePng } from '../helpers/uniqueImage';
import { BasePage } from './BasePage';

export class UploadPage extends BasePage {
  static readonly URL = '/upload';

  readonly fileInput = this.page.locator('input[type="file"]');
  readonly publicVisibilityButton = this.page.getByRole('button', {
    name: 'Public',
  });
  readonly privateVisibilityButton = this.page.getByRole('button', {
    name: 'Private',
  });
  readonly visibilityToggle = this.page
    .getByRole('button', { name: /^(Public|Private)$/ })
    .first();

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
    await this.heading.waitFor({ state: 'visible' });
    await this.fileInput.setInputFiles({
      name: `e2e-${Date.now()}.txt`,
      mimeType: 'text/plain',
      buffer: Buffer.from('not an image'),
    });
  }

  async uploadMultipleImages(count: number) {
    await this.heading.waitFor({ state: 'visible' });

    const files = Array.from({ length: count }, () => {
      const { buffer, name } = createUniquePng();
      return { name, mimeType: 'image/png', buffer };
    });

    await expect(async () => {
      const responsePromise = this.page.waitForResponse(
        isChunkedUploadCompletionResponse,
        { timeout: 1500 },
      );
      await this.fileInput.setInputFiles(files);
      await responsePromise;
    }).toPass({ timeout: 15000 });
  }

  async uploadFiles(paths: string[]) {
    await this.fileInput.setInputFiles(paths);
  }

  async uploadFixtureFile(path: string, mimeType: string): Promise<Response> {
    const name = basename(path);
    const buffer = readFileSync(path);

    return this.uploadBuffer({ name, mimeType, buffer });
  }

  async uploadBuffer(file: {
    name: string;
    mimeType: string;
    buffer: Buffer;
  }): Promise<Response> {
    await this.heading.waitFor({ state: 'visible' });

    let completion: Response | undefined;

    await expect(async () => {
      if (!/\/upload(\/|$|\?)/.test(this.page.url())) {
        return;
      }
      const responsePromise = this.page.waitForResponse(
        isChunkedUploadCompletionResponse,
        { timeout: 10000 },
      );
      await this.fileInput.setInputFiles(file);
      completion = await responsePromise;
    }).toPass({ timeout: 30000 });

    if (!completion) {
      throw new Error('Chunked upload did not complete');
    }

    return completion;
  }

  async uploadSingleImage() {
    await this.heading.waitFor({ state: 'visible' });

    const { buffer, name } = createUniquePng();

    await expect(async () => {
      const requestPromise = this.page.waitForRequest(
        (request) =>
          /\/api\/upload\/chunked$/.test(request.url()) &&
          request.method() === 'POST',
        { timeout: 1500 },
      );
      await this.fileInput.setInputFiles({
        name,
        mimeType: 'image/png',
        buffer,
      });
      await requestPromise;
    }).toPass({ timeout: 15000 });
  }

  async uploadFilesExpectingRequest(
    files: { name: string; mimeType: string; buffer: Buffer }[],
  ) {
    await this.heading.waitFor({ state: 'visible' });

    await expect(async () => {
      const requestPromise = this.page.waitForRequest(
        (request) =>
          /\/api\/upload\/chunked$/.test(request.url()) &&
          request.method() === 'POST',
        { timeout: 1500 },
      );
      await this.fileInput.setInputFiles(files);
      await requestPromise;
    }).toPass({ timeout: 15000 });
  }

  async uploadUniqueImage() {
    await this.heading.waitFor({ state: 'visible' });

    const { buffer, name } = createUniquePng();

    await expect(async () => {
      const responsePromise = this.page.waitForResponse(
        isChunkedUploadCompletionResponse,
        { timeout: 1500 },
      );
      await this.fileInput.setInputFiles({
        name,
        mimeType: 'image/png',
        buffer,
      });
      await responsePromise;
    }).toPass({ timeout: 15000 });
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
}
