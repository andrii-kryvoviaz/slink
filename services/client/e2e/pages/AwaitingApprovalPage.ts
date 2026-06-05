import type { Page } from '@playwright/test';

import { BasePage } from './BasePage';

export class AwaitingApprovalPage extends BasePage {
  static readonly URL = '/profile/awaiting-approval';

  constructor(page: Page) {
    super(page);
  }

  get reviewHeading() {
    return this.page.getByRole('heading', { name: 'Review in Progress' });
  }

  get approvedHeading() {
    return this.page.getByRole('heading', { name: 'Welcome Aboard' });
  }

  get signInLink() {
    return this.page.getByRole('link', { name: 'Sign In' });
  }

  async goto() {
    await this.page.goto(AwaitingApprovalPage.URL);
  }
}
