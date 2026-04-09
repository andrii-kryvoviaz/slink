import type { Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class LoginPage extends BasePage {
  static readonly URL = '/profile/login';

  readonly usernameInput = this.page.getByPlaceholder(
    'Enter your email or username',
  );
  readonly passwordInput = this.page.getByPlaceholder('Enter your password');
  readonly submitButton = this.page.getByRole('button', { name: 'Sign In' });

  constructor(page: Page) {
    super(page);
  }

  get heading() {
    return this.page.getByRole('heading', { name: 'Welcome back' });
  }

  async goto() {
    await this.page.goto(LoginPage.URL);
  }

  async login(username: string, password: string) {
    await this.goto();
    await this.heading.waitFor({ state: 'visible' });
    await this.usernameInput.fill(username);
    await this.fillField(this.passwordInput, password);
    await this.submitButton.click();
  }
}
