import { defineConfig, devices } from '@playwright/test';

import { resolveBaseURLFromEnv } from './e2e/helpers/session';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? [['html'], ['github']] : 'html',
  use: {
    baseURL: resolveBaseURLFromEnv(),
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'provision',
      testMatch: '**/provision.setup.ts',
    },
    {
      name: 'chromium',
      grepInvert: /@serial/,
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['provision'],
    },
    {
      name: 'shared-state-mutating',
      grep: /@serial/,
      fullyParallel: false,
      workers: 1,
      use: { ...devices['Desktop Chrome'] },
      dependencies: ['chromium'],
    },
  ],
});
