import { defineConfig, devices } from '@playwright/test';

import { resolveBaseURLFromEnv } from './e2e/helpers/session';

const project = process.env.COMPOSE_PROJECT_NAME ?? 'slink-e2e';
const html = [
  'html',
  { outputFolder: `playwright-report/${project}` },
] as const;

export default defineConfig({
  testDir: './e2e',
  fullyParallel: true,
  workers: '100%',
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  outputDir: `test-results/${project}`,
  reporter: process.env.CI ? [html, ['github']] : [html],
  use: {
    baseURL: resolveBaseURLFromEnv(),
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    serviceWorkers: 'block',
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
