import type { Page } from '@playwright/test';

import type { Account } from './accounts';

const LOGIN_PATH = '/profile/login';
const INLINED_DEMO_SETTINGS =
  /demo:\{enabled:false,demoUsername:"[^"]*",demoPassword:"[^"]*"\}/g;

function demoSettings({ username, password }: Account) {
  const demoUsername = JSON.stringify(username);
  const demoPassword = JSON.stringify(password);

  return `demo:{enabled:true,demoUsername:${demoUsername},demoPassword:${demoPassword}}`;
}

export async function stageDemoMode(page: Page, account: Account) {
  await page.route(
    (url) => url.pathname === LOGIN_PATH,
    async (route) => {
      const request = route.request();

      if (request.method() !== 'GET' || !request.isNavigationRequest()) {
        await route.fallback();
        return;
      }

      const response = await route.fetch();
      const html = await response.text();

      const body = html.replace(INLINED_DEMO_SETTINGS, demoSettings(account));

      if (body === html) {
        throw new Error('The login document has no inlined demo settings');
      }

      await route.fulfill({ response, body });
    },
  );
}
