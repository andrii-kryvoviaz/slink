import type { Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class SignupPage extends BasePage {
  static readonly URL = '/profile/signup';

  readonly usernameInput = this.page.getByPlaceholder('Choose a username');
  readonly emailInput = this.page.getByPlaceholder('Enter your email');
  readonly passwordInput = this.page.getByPlaceholder(
    'Create a strong password',
  );
  readonly confirmPasswordInput = this.page.getByPlaceholder(
    'Confirm your password',
  );
  readonly submitButton = this.page.getByRole('button', {
    name: 'Create Account',
  });

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Create Account' });
  }

  async goto() {
    await this.page.goto(SignupPage.URL);
  }

  async signup(data: {
    username: string;
    email: string;
    password: string;
    confirm: string;
  }) {
    await this.goto();
    await this.heading.waitFor({ state: 'visible' });
    await this.usernameInput.fill(data.username);
    await this.emailInput.fill(data.email);
    await this.fillField(this.passwordInput, data.password);
    await this.fillField(this.confirmPasswordInput, data.confirm);
    await this.submitButton.click();
  }
}
