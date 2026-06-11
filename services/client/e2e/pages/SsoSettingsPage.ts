import { type Locator } from '@playwright/test';

import { BasePage } from './BasePage';

export type RegistrationPolicyLabel = 'Global' | 'Allowed' | 'Blocked';
export type ApprovalPolicyLabel = 'Global' | 'Required' | 'Auto-approve';

export class SsoSettingsPage extends BasePage {
  static readonly LIST_URL = '/admin/settings/sso';

  get registrationPolicyGroup(): Locator {
    return this.page.getByRole('radiogroup', {
      name: 'New user registration policy',
    });
  }

  get approvalPolicyGroup(): Locator {
    return this.page.getByRole('radiogroup', {
      name: 'Admin approval policy',
    });
  }

  registrationPolicyRadio(label: RegistrationPolicyLabel): Locator {
    return this.registrationPolicyGroup.getByRole('radio', {
      name: `Select ${label}`,
      exact: true,
    });
  }

  approvalPolicyRadio(label: ApprovalPolicyLabel): Locator {
    return this.approvalPolicyGroup.getByRole('radio', {
      name: `Select ${label}`,
      exact: true,
    });
  }

  get customProviderTile(): Locator {
    return this.providerTileGrid
      .getByRole('button')
      .filter({ hasNotText: /\S/ });
  }

  get addProviderButton(): Locator {
    return this.page.getByRole('button', { name: 'Add Provider' });
  }

  get updateProviderButton(): Locator {
    return this.page.getByRole('button', { name: 'Update Provider' });
  }

  providerRow(slug: string): Locator {
    return this.page
      .locator('div', { has: this.page.getByText(slug, { exact: true }) })
      .filter({ has: this.page.getByRole('switch') })
      .last();
  }

  async gotoList() {
    await this.page.goto(SsoSettingsPage.LIST_URL);
  }

  async gotoNew() {
    await this.page.goto(`${SsoSettingsPage.LIST_URL}/new`);
  }

  async gotoEdit(id: string) {
    await this.page.goto(`${SsoSettingsPage.LIST_URL}/${id}/edit`);
  }

  async waitForList() {
    await this.page.waitForURL(`**${SsoSettingsPage.LIST_URL}`);
  }

  async fillCustomProviderForm(fields: {
    name: string;
    slug: string;
    discoveryUrl: string;
    clientId: string;
    clientSecret: string;
  }) {
    await this.page.getByPlaceholder('e.g. My SSO Provider').fill(fields.name);
    await this.page.getByPlaceholder('e.g. my-provider').fill(fields.slug);
    await this.page
      .getByPlaceholder('https://idp.example.com')
      .fill(fields.discoveryUrl);
    await this.page.getByPlaceholder('OAuth Client ID').fill(fields.clientId);
    await this.page
      .getByPlaceholder('OAuth Client Secret')
      .fill(fields.clientSecret);
  }

  private get providerTileGrid(): Locator {
    return this.page
      .locator('div', {
        has: this.page.getByRole('button', { name: 'Google' }),
      })
      .last();
  }
}
