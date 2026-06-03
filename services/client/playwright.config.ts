import { defineConfig, devices } from '@playwright/test';

export const USER_STORAGE_STATE = 'e2e/.auth/user.json';
export const SERIAL_STORAGE_STATE = 'e2e/.auth/serial.json';

export default defineConfig({
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
      name: 'provision',
      testMatch: '**/provision.setup.ts',
    },
    {
      name: 'setup',
      testMatch: '**/auth.setup.ts',
      dependencies: ['provision'],
    },
    {
      name: 'chromium',
      grepInvert: /@serial/,
      use: {
        ...devices['Desktop Chrome'],
        storageState: USER_STORAGE_STATE,
      },
      dependencies: ['setup'],
    },
    {
      name: 'serial-setup',
      testMatch: '**/serial-auth.setup.ts',
      dependencies: ['chromium'],
    },
    {
      name: 'shared-state-mutating',
      grep: /@serial/,
      fullyParallel: false,
      workers: 1,
      use: {
        ...devices['Desktop Chrome'],
        storageState: SERIAL_STORAGE_STATE,
      },
      dependencies: ['serial-setup'],
    },
  ],
});
