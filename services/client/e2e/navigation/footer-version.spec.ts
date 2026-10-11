import type { Page } from '@playwright/test';

import { expect, test } from '../fixtures/auth.fixture';

const GITHUB_API = 'https://api.github.com/**';
const SEMVER = /^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/;
const stackVersion = process.env.E2E_SLINK_VERSION || 'dev';
const isSemverStack = SEMVER.test(stackVersion);

async function stubLatestRelease(page: Page, version: string) {
  const requests: string[] = [];

  await page.route(GITHUB_API, async (route) => {
    requests.push(route.request().url());
    await route.fulfill({
      json: {
        tag_name: `v${version}`,
        name: `v${version}`,
        body: '',
        published_at: '2026-10-01T00:00:00Z',
        html_url: `https://github.com/andrii-kryvoviaz/slink/releases/tag/v${version}`,
        prerelease: false,
        draft: false,
      },
    });
  });

  return requests;
}

test.describe('Footer version', { tag: ['@anonymous', '@sc-901'] }, () => {
  test.use({ serviceWorkers: 'block' });

  test('shows a non-semver build name without a v and never contacts GitHub', async ({
    page,
  }) => {
    test.skip(isSemverStack, 'stack runs a semver SLINK_VERSION');
    const requests = await stubLatestRelease(page, '1.15.0');
    const footer = page.locator('footer');

    await page.goto('/explore');
    await page.waitForLoadState('networkidle');

    await expect
      .soft(footer.getByText(stackVersion, { exact: true }))
      .toBeVisible();
    expect.soft(requests).toEqual([]);
    await expect(footer.getByText('Update Available')).toHaveCount(0);
  });

  test('shows a semver version with a v and checks the latest GitHub release', async ({
    page,
  }) => {
    test.skip(!isSemverStack, 'needs E2E_SLINK_VERSION=<semver>');
    const requests = await stubLatestRelease(page, stackVersion);
    const footer = page.locator('footer');

    await page.goto('/explore');
    await page.waitForLoadState('networkidle');

    await expect
      .soft(footer.getByText(`v${stackVersion}`, { exact: true }))
      .toBeVisible();
    expect.soft(requests).toHaveLength(1);
    expect.soft(requests[0]).toMatch(/\/releases\/latest$/);
    await expect(footer.getByText('Update Available')).toHaveCount(0);
  });
});
