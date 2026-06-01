import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  globalSetup: './e2e/global-setup.ts',
  testDir: './e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI ? [['html'], ['github']] : 'html',
  use: {
    baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:3100',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /.*\.setup\.ts/,
    },
    {
      name: 'chromium',
      testIgnore: [
        /admin\/settings-save\.spec\.ts/,
        /preferences\/locale\.spec\.ts/,
      ],
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'e2e/.auth/user.json',
      },
      dependencies: ['setup'],
    },
    {
      name: 'shared-state-mutating',
      testMatch: [
        /admin\/settings-save\.spec\.ts/,
        /preferences\/locale\.spec\.ts/,
      ],
      fullyParallel: false,
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'e2e/.auth/user.json',
      },
      dependencies: ['chromium'],
    },
  ],
});
