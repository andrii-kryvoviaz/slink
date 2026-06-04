import { type Locator, type Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class ProfilePage extends BasePage {
  static readonly URL = '/profile';

  readonly oldPasswordInput = this.page.getByPlaceholder('Current password');
  readonly newPasswordInput = this.page.getByPlaceholder('New password');
  readonly confirmPasswordInput =
    this.page.getByPlaceholder('Confirm password');
  readonly updatePasswordButton = this.page.getByRole('button', {
    name: 'Update Password',
  });

  constructor(page: Page) {
    super(page);
  }

  async goto() {
    await this.page.goto(ProfilePage.URL);
    await this.oldPasswordInput.waitFor({ state: 'visible' });
  }

  oldPasswordError(): Locator {
    return this.oldPasswordInput
      .locator('xpath=ancestor::div[1]/following-sibling::div')
      .first();
  }

  async changePassword(oldPassword: string, newPassword: string) {
    await this.fillField(this.oldPasswordInput, oldPassword);
    await this.fillField(this.newPasswordInput, newPassword);
    await this.fillField(this.confirmPasswordInput, newPassword);
    await this.updatePasswordButton.click();
  }
}
