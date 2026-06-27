import { type Locator, type Page, expect } from '@playwright/test';

import { BasePage } from './BasePage';

export type StorageProviderLabel =
  'Local Storage' | 'Network Storage (SMB)' | 'Amazon S3';

export type SmbCredentials = {
  host: string;
  share: string;
  workgroup: string;
  username: string;
  password: string;
};

export class StorageSettingsPage extends BasePage {
  static readonly URL = '/admin/settings/storage';

  readonly providerTrigger = this.page
    .locator('[data-slot="select-trigger"]')
    .first();

  readonly smbHost = this.inputByName('smbHost');
  readonly smbShare = this.inputByName('smbShare');
  readonly smbWorkgroup = this.inputByName('smbWorkgroup');
  readonly smbUsername = this.inputByName('smbUsername');
  readonly smbPassword = this.inputByName('smbPassword');

  constructor(page: Page) {
    super(page);
  }

  get heading(): Locator {
    return this.page.getByRole('heading', { name: 'Storage', level: 1 });
  }

  get errorToast(): Locator {
    return this.page.locator('[data-sonner-toast]');
  }

  async goto() {
    await this.page.goto(StorageSettingsPage.URL);
  }

  inputByName(name: string): Locator {
    return this.page.locator(`input[name="${name}"]`);
  }

  async selectProvider(label: StorageProviderLabel) {
    const option = this.page.getByRole('option', { name: label, exact: true });

    await expect(async () => {
      await this.providerTrigger.click();
      await expect(option).toBeVisible({ timeout: 1000 });
    }).toPass({ timeout: 15000 });

    await option.click();
  }

  async fillSmbCredentials(credentials: SmbCredentials) {
    await this.smbHost.fill(credentials.host);
    await this.smbShare.fill(credentials.share);
    await this.smbWorkgroup.fill(credentials.workgroup);
    await this.smbUsername.fill(credentials.username);
    await this.smbPassword.fill(credentials.password);
  }

  async save() {
    const pane = this.page
      .locator('form')
      .filter({ has: this.page.getByRole('button', { name: 'Save Changes' }) })
      .first();
    const saveButton = pane.getByRole('button', { name: 'Save Changes' });

    await expect(async () => {
      const responsePromise = this.page.waitForResponse(
        (response) =>
          response.url().includes('/api/settings') &&
          response.request().method() === 'POST',
        { timeout: 1000 },
      );
      await saveButton.click();
      await responsePromise;
    }).toPass({ timeout: 15000 });
  }
}
