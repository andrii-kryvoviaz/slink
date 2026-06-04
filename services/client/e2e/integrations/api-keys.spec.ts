import { deflateSync } from 'zlib';

import { expect, test } from '../fixtures/auth.fixture';
import { IntegrationsPage } from '../pages/IntegrationsPage';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';

function crc32(buf: Buffer): number {
  let crc = 0xffffffff;
  for (const byte of buf) {
    crc ^= byte;
    for (let i = 0; i < 8; i++) {
      crc = crc & 1 ? (crc >>> 1) ^ 0xedb88320 : crc >>> 1;
    }
  }
  return (crc ^ 0xffffffff) >>> 0;
}

function chunk(type: string, data: Buffer): Buffer {
  const length = Buffer.alloc(4);
  length.writeUInt32BE(data.length, 0);
  const typeAndData = Buffer.concat([Buffer.from(type, 'ascii'), data]);
  const crc = Buffer.alloc(4);
  crc.writeUInt32BE(crc32(typeAndData), 0);
  return Buffer.concat([length, typeAndData, crc]);
}

function uniquePng(): Buffer {
  const width = 2;
  const height = 2;

  const ihdr = Buffer.alloc(13);
  ihdr.writeUInt32BE(width, 0);
  ihdr.writeUInt32BE(height, 4);
  ihdr.writeUInt8(8, 8);
  ihdr.writeUInt8(2, 9);

  const raw = Buffer.alloc(height * (1 + width * 3));
  for (let y = 0; y < height; y++) {
    const rowStart = y * (1 + width * 3);
    raw[rowStart] = 0;
    for (let x = 0; x < width; x++) {
      const pixel = rowStart + 1 + x * 3;
      raw[pixel] = Math.floor(Math.random() * 256);
      raw[pixel + 1] = Math.floor(Math.random() * 256);
      raw[pixel + 2] = Math.floor(Math.random() * 256);
    }
  }

  return Buffer.concat([
    Buffer.from([0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a]),
    chunk('IHDR', ihdr),
    chunk('IDAT', deflateSync(raw)),
    chunk('IEND', Buffer.alloc(0)),
  ]);
}

async function externalUpload(apiKey: string): Promise<number> {
  const form = new FormData();
  form.append(
    'image',
    new Blob([uniquePng()], { type: 'image/png' }),
    'pixel.png',
  );

  const response = await fetch(`${API_URL}/api/external/upload`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${apiKey}` },
    body: form,
  });

  return response.status;
}

test.describe('API key management', () => {
  test('creates a key, shows it once, then hides the raw value', async ({
    page,
  }) => {
    const integrationsPage = new IntegrationsPage(page);
    const keyName = `e2e-show-once-${Date.now()}`;

    await integrationsPage.goto();

    const rawKey = await integrationsPage.createApiKey(keyName);
    expect(rawKey).toMatch(/^sk_/);

    await integrationsPage.closeCreatedKeyDialog();

    await expect(page.getByText(rawKey)).toHaveCount(0);
    await expect(page.locator('input[readonly]')).toHaveCount(0);

    await expect(integrationsPage.card(keyName)).toBeVisible();
  });

  test('revokes a key and rejects it immediately at the backend', async ({
    page,
  }) => {
    const integrationsPage = new IntegrationsPage(page);
    const keyName = `e2e-revoke-${Date.now()}`;

    await integrationsPage.goto();

    const rawKey = await integrationsPage.createApiKey(keyName);
    await integrationsPage.closeCreatedKeyDialog();

    await expect(integrationsPage.card(keyName)).toBeVisible();

    expect(await externalUpload(rawKey)).toBe(201);

    await integrationsPage.revoke(keyName);
    await expect(integrationsPage.card(keyName)).toHaveCount(0);

    await expect
      .poll(async () => externalUpload(rawKey), { timeout: 10000 })
      .toBe(401);
  });
});
