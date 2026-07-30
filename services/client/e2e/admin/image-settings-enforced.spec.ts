import { expect, test } from '../fixtures/auth.fixture';
import { createUniquePng } from '../helpers/uniqueImage';
import { ImageSettingsPage } from '../pages/ImageSettingsPage';

const JPEG_ONLY_MASK = 1 << 1;
const OVERSIZED_PADDING_BYTES = 3 * 1024 * 1024;
const INIT_UPLOAD_URL = /\/api\/upload\/chunked$/;

const oversizedPng = () => {
  const { buffer, name } = createUniquePng();

  return {
    name,
    mimeType: 'image/png',
    buffer: Buffer.concat([buffer, Buffer.alloc(OVERSIZED_PADDING_BYTES)]),
  };
};

test.describe('Image settings enforcement', { tag: '@serial' }, () => {
  test('rejects an upload larger than the max size saved in the admin form', async ({
    page,
    api,
    settingsApi,
    uploadPage,
  }) => {
    const defaultSettings = await api.settings.getDefaultSettings();
    const defaultMaxSize: string = defaultSettings?.image?.maxSize;

    expect(typeof defaultMaxSize).toBe('string');

    const unit = defaultMaxSize.replace(/[\d\s]/g, '');
    const amount = parseInt(defaultMaxSize, 10) === 1 ? '2' : '1';

    await settingsApi.set('image', { maxSize: defaultMaxSize });

    const imageSettingsPage = new ImageSettingsPage(page);
    await imageSettingsPage.goto();
    await imageSettingsPage.fillMaxSize(amount);
    await imageSettingsPage.save();

    await expect
      .poll(async () => {
        const current = await api.settings.getSettings();
        return current.image?.maxSize;
      })
      .toBe(`${amount}${unit}`);

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    const rejection = page.waitForResponse(
      (response) =>
        INIT_UPLOAD_URL.test(response.url()) &&
        response.request().method() === 'POST',
    );

    await uploadPage.uploadFilesExpectingRequest([oversizedPng()]);

    expect((await rejection).status()).toBe(413);

    const toast = await uploadPage.waitForToast();
    await expect(toast).toContainText(/too large/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
    await expect(page).toHaveURL(/\/upload/);
  });

  test('rejects an upload in a format removed from the allowed formats', async ({
    page,
    settingsApi,
    uploadPage,
  }) => {
    await settingsApi.set('image', { allowedFormats: JPEG_ONLY_MASK });

    let uploadRequested = false;
    page.on('request', (request) => {
      if (INIT_UPLOAD_URL.test(request.url()) && request.method() === 'POST') {
        uploadRequested = true;
      }
    });

    await uploadPage.goto();
    await expect(uploadPage.heading).toBeVisible();

    await expect(page.getByText('Supported formats')).toBeVisible();
    await expect(page.getByText('PNG')).toHaveCount(0);

    const { buffer, name } = createUniquePng();

    await expect(async () => {
      await uploadPage.fileInput.setInputFiles([]);
      await uploadPage.fileInput.setInputFiles({
        name,
        mimeType: 'image/png',
        buffer,
      });
      await expect(uploadPage.getToast()).toBeAttached({ timeout: 2000 });
    }).toPass({ timeout: 15000 });

    const toast = uploadPage.getToast();
    await expect(toast).toContainText('Unsupported file format');
    await expect(toast).toContainText(/not supported/i);

    await expect(uploadPage.successHeading).toBeHidden();
    await expect(uploadPage.multiUploadHeading).toBeHidden();
    await expect(uploadPage.heading).toBeVisible();
    await expect(page).toHaveURL(/\/upload/);
    expect(uploadRequested).toBe(false);
  });
});
