import { expect, test } from '../fixtures/auth.fixture';
import { createSsoProvider, deleteSsoProviders } from '../helpers/ssoProviders';
import type { UploadPage } from '../pages/UploadPage';

const PREFIX = 'e2e-guest-success';

const expectNoGuestOffer = async (uploadPage: UploadPage) => {
  await expect(uploadPage.createAccountLink).toHaveCount(0);
  await expect(uploadPage.guestNoticeHeading).toHaveCount(0);
  await expect(uploadPage.guestNoticeBody).toHaveCount(0);
};

test.describe(
  'Guest upload success screen',
  { tag: ['@serial', '@anonymous', '@sc-1188'] },
  () => {
    test.beforeEach(async ({ api, settingsApi }) => {
      await createSsoProvider(api, PREFIX);
      await settingsApi.set('access', {
        allowGuestUploads: true,
        allowUnauthenticatedAccess: false,
      });
    });

    test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

    test('offers Create Account and the notice when a guest can sign up', async ({
      settingsApi,
      uploadPage,
    }) => {
      await settingsApi.set('user', {
        allowRegistration: true,
        localAuthEnabled: true,
      });

      await uploadPage.goto();
      await uploadPage.uploadUniqueImage();
      await uploadPage.waitForSuccess();

      await expect(uploadPage.uploadAnotherButton).toBeVisible();
      await expect(uploadPage.createAccountLink).toHaveAttribute(
        'href',
        '/profile/signup',
      );
      await expect(uploadPage.guestNoticeHeading).toBeVisible();
      await expect(uploadPage.guestNoticeBody).toBeVisible();
      await expect(uploadPage.viewHistoryAction).toHaveCount(0);
    });

    const signupUnavailable = [
      {
        name: 'local auth off, registration allowed',
        user: { allowRegistration: true, localAuthEnabled: false },
      },
      {
        name: 'local auth off, registration closed',
        user: { allowRegistration: false, localAuthEnabled: false },
      },
      {
        name: 'registration closed, local auth on',
        user: { allowRegistration: false, localAuthEnabled: true },
      },
    ];

    for (const { name, user } of signupUnavailable) {
      test(`leaves only Upload Another Image with ${name}`, async ({
        settingsApi,
        uploadPage,
      }) => {
        await settingsApi.set('user', user);

        await uploadPage.goto();
        await uploadPage.uploadUniqueImage();
        await uploadPage.waitForSuccess();

        await expect(uploadPage.uploadAnotherButton).toBeVisible();
        await expect(uploadPage.viewHistoryAction).toHaveCount(0);
        await expectNoGuestOffer(uploadPage);
      });
    }
  },
);

test.describe(
  'Signed-in upload with signup unavailable',
  { tag: ['@serial', '@sc-1188'] },
  () => {
    test.afterEach(({ api }) => deleteSsoProviders(api, PREFIX));

    test('lands on the image page with no guest offer', async ({
      page,
      api,
      settingsApi,
      uploadPage,
    }) => {
      await createSsoProvider(api, PREFIX);
      await settingsApi.set('user', {
        allowRegistration: false,
        localAuthEnabled: false,
      });

      await uploadPage.goto();
      await uploadPage.uploadUniqueImage();
      await uploadPage.waitForUploadComplete();

      await expect(page).toHaveURL(/\/info\//);
      await expectNoGuestOffer(uploadPage);
    });
  },
);
