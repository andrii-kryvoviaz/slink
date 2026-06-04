import { Locale } from '../helpers/Locale';
import { ApiClient } from '../helpers/api';
import { provisionUser } from '../helpers/auth';
import {
  type TestUser,
  resolveTestUser,
  uniqueUser,
} from '../helpers/testUsers';
import { AdminSettingsPage } from '../pages/AdminSettingsPage';
import { AwaitingApprovalPage } from '../pages/AwaitingApprovalPage';
import { CollectionsPage } from '../pages/CollectionsPage';
import { ExplorePage } from '../pages/ExplorePage';
import { HistoryPage } from '../pages/HistoryPage';
import { ImageInfoPage } from '../pages/ImageInfoPage';
import { LayoutControls } from '../pages/LayoutControls';
import { LoginPage } from '../pages/LoginPage';
import { PreferencesPage } from '../pages/PreferencesPage';
import { SharePage } from '../pages/SharePage';
import { SharesPage } from '../pages/SharesPage';
import { SignupPage } from '../pages/SignupPage';
import { UploadPage } from '../pages/UploadPage';
import { test as base, expect } from './worker-auth.fixture';

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
  testUser: TestUser;
  loginPage: LoginPage;
  signupPage: SignupPage;
  awaitingApprovalPage: AwaitingApprovalPage;
  uploadPage: UploadPage;
  sharePage: SharePage;
  sharesPage: SharesPage;
  imageInfoPage: ImageInfoPage;
  explorePage: ExplorePage;
  historyPage: HistoryPage;
  collectionsPage: CollectionsPage;
  preferencesPage: PreferencesPage;
  adminSettingsPage: AdminSettingsPage;
  layoutControls: LayoutControls;
  api: ApiClient;
  actor: (label?: string) => Promise<ApiClient>;
  localeHelper: Locale;
  settingsApi: SettingsApi;
};

export const test = base.extend<AuthFixtures>({
  testUser: async ({}, use) => {
    await use(
      resolveTestUser(test.info().project.name, test.info().parallelIndex),
    );
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

  sharesPage: async ({ page }, use) => {
    await use(new SharesPage(page));
  },

  imageInfoPage: async ({ page }, use) => {
    await use(new ImageInfoPage(page));
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

  api: async ({ workerApi }, use) => {
    await use(workerApi);
  },

  actor: async ({}, use) => {
    await use((label = 'actor') => provisionUser(uniqueUser(label)));
  },

  localeHelper: async ({ page, api }, use) => {
    await use(new Locale(page, api));
  },

  settingsApi: async ({ api }, use) => {
    const pending: Array<{ category: string; settings: object }> = [];

    await use({
      set: async (category: string, settings: Record<string, unknown>) => {
        const allSettings = await api.settings.getSettings();
        const current = allSettings[category] ?? {};
        pending.push({ category, settings: { ...current } });
        const merged = deepMerge(current, settings as Record<string, any>);
        await api.settings.updateSettings(category, merged);
      },
    });

    for (const { category, settings } of pending) {
      await api.settings.updateSettings(category, settings);
    }
  },
});

export { expect };
