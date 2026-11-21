import { expect, test } from '../fixtures/auth.fixture';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const PARALLEL_LOADS = 16;

test.describe('Concurrent image views', () => {
  test(
    'parallel anonymous loads of a public image all succeed and all count',
    { tag: '@sc-1203' },
    async ({ api }) => {
      const imageId = await api.content.uploadImage({ isPublic: true });

      const statuses = await Promise.all(
        Array.from({ length: PARALLEL_LOADS }, async () => {
          const response = await fetch(
            `${API_URL}/api/image/public/${imageId}.png`,
          );
          await response.arrayBuffer();

          return response.status;
        }),
      );

      expect(statuses).toEqual(Array(PARALLEL_LOADS).fill(200));

      const { data } = await api.content.getImage(imageId);
      const detail = data as { views?: number; data?: { views?: number } };

      expect(detail.data?.views ?? detail.views).toBe(PARALLEL_LOADS);
    },
  );
});
