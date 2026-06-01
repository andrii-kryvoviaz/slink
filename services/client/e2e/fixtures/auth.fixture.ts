import { test as base, expect } from '@playwright/test';

import { AdminApiClient } from '../helpers/AdminApiClient';
import { ContentApiClient } from '../helpers/ContentApiClient';
import { AdminSettingsPage } from '../pages/AdminSettingsPage';
import { AwaitingApprovalPage } from '../pages/AwaitingApprovalPage';
import { CollectionsPage } from '../pages/CollectionsPage';
import { ExplorePage } from '../pages/ExplorePage';
import { HistoryPage } from '../pages/HistoryPage';
import { LayoutControls } from '../pages/LayoutControls';
import { LoginPage } from '../pages/LoginPage';
import { PreferencesPage } from '../pages/PreferencesPage';
import { SharePage } from '../pages/SharePage';
import { SignupPage } from '../pages/SignupPage';
import { UploadPage } from '../pages/UploadPage';

function deepMerge(
  target: Record<string, any>,
  source: Record<string, any>,
): Record<string, any> {
  const result = { ...target };
  for (const key of Object.keys(source)) {
    if (
      source[key] &&
      typeof source[key] === 'object' &&
      !Array.isArray(source[key]) &&
      target[key] &&
      typeof target[key] === 'object'
    ) {
      result[key] = deepMerge(target[key], source[key]);
    } else {
      result[key] = source[key];
    }
  }
  return result;
}

type SettingsApi = {
  set: (category: string, settings: Record<string, unknown>) => Promise<void>;
};

type AuthFixtures = {
  testUser: { username: string; password: string };
  loginPage: LoginPage;
  signupPage: SignupPage;
  awaitingApprovalPage: AwaitingApprovalPage;
  uploadPage: UploadPage;
  sharePage: SharePage;
  explorePage: ExplorePage;
  historyPage: HistoryPage;
  collectionsPage: CollectionsPage;
  preferencesPage: PreferencesPage;
  adminSettingsPage: AdminSettingsPage;
  layoutControls: LayoutControls;
  adminApi: AdminApiClient;
  contentApi: ContentApiClient;
  settingsApi: SettingsApi;
};

export const test = base.extend<AuthFixtures>({
  testUser: async ({}, use) => {
    await use({
      username: 'e2e',
      password: 'E2eTest123!',
    });
  },

  loginPage: async ({ page }, use) => {
    await use(new LoginPage(page));
  },

  signupPage: async ({ page }, use) => {
    await use(new SignupPage(page));
  },

  awaitingApprovalPage: async ({ page }, use) => {
    await use(new AwaitingApprovalPage(page));
  },

  uploadPage: async ({ page }, use) => {
    await use(new UploadPage(page));
  },

  sharePage: async ({ page }, use) => {
    await use(new SharePage(page));
  },

  explorePage: async ({ page }, use) => {
    await use(new ExplorePage(page));
  },

  historyPage: async ({ page }, use) => {
    await use(new HistoryPage(page));
  },

  collectionsPage: async ({ page }, use) => {
    await use(new CollectionsPage(page));
  },

  preferencesPage: async ({ page }, use) => {
    await use(new PreferencesPage(page));
  },

  adminSettingsPage: async ({ page }, use) => {
    await use(new AdminSettingsPage(page));
  },

  layoutControls: async ({ page }, use) => {
    await use(new LayoutControls(page));
  },

  adminApi: async ({}, use) => {
    await use(await AdminApiClient.create());
  },

  contentApi: async ({}, use) => {
    await use(await ContentApiClient.create());
  },

  settingsApi: async ({ adminApi }, use) => {
    const pending: Array<{ category: string; settings: object }> = [];

    await use({
      set: async (category: string, settings: Record<string, unknown>) => {
        const allSettings = await adminApi.getSettings();
        const current = allSettings[category] ?? {};
        pending.push({ category, settings: { ...current } });
        const merged = deepMerge(current, settings as Record<string, any>);
        await adminApi.updateSettings(category, merged);
      },
    });

    for (const { category, settings } of pending) {
      await adminApi.updateSettings(category, settings);
    }
  },
});

export { expect };
