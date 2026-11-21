import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const ROUNDS = 8;

type CollectionItem = { itemId: string; position: number };
type BatchResult = { processed: string[]; failed: Array<{ id: string }> };

async function send(
  token: string,
  method: string,
  path: string,
  body?: object,
): Promise<Response> {
  return fetch(`${API_URL}${path}`, {
    method,
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: body === undefined ? undefined : JSON.stringify(body),
  });
}

async function listItems(
  token: string,
  collectionId: string,
): Promise<CollectionItem[]> {
  const response = await send(
    token,
    'GET',
    `/api/collection/${collectionId}/items`,
  );
  const body = await response.json();

  return (body.data ?? body) as CollectionItem[];
}

async function listTagIds(token: string, imageId: string): Promise<string[]> {
  const response = await send(token, 'GET', `/api/images/${imageId}/tags`);
  const body = await response.json();

  return ((body.data ?? body) as Array<{ id: string }>).map((tag) => tag.id);
}

test.describe('Concurrent item writes', () => {
  test('parallel batch reassigns and single adds leave distinct positions and keep every acknowledged item', async () => {
    const api = await provisionUser(unique('itemwrites'));
    const collectionId = await api.content.createCollection({
      name: 'concurrent-item-writes',
    });

    for (let round = 0; round < ROUNDS; round++) {
      const batchImageId = await api.content.uploadImage();
      const singleImageId = await api.content.uploadImage();

      const [batch, single] = await Promise.all([
        send(api.token, 'PUT', '/api/images/batch', {
          assignments: {
            [batchImageId]: { collectionIds: [collectionId] },
          },
        }),
        send(
          api.token,
          'POST',
          `/api/collection/${collectionId}/items/${singleImageId}`,
        ),
      ]);

      expect([200, 409], `round ${round} batch`).toContain(batch.status);
      expect([201, 409], `round ${round} single`).toContain(single.status);

      const acknowledged: string[] = [];
      if (batch.status === 200) {
        const body = await batch.json();
        const result = (body.data ?? body) as BatchResult;
        acknowledged.push(...result.processed);
      }
      if (single.status === 201) {
        acknowledged.push(singleImageId);
      }

      const items = await listItems(api.token, collectionId);
      const positions = items.map((item) => item.position);

      expect(new Set(positions).size, `round ${round}`).toBe(items.length);
      for (const imageId of acknowledged) {
        expect(
          items.map((item) => item.itemId),
          `round ${round}`,
        ).toContain(imageId);
      }
    }
  });

  test('parallel tag writes on one image keep exactly the tags that were acknowledged', async () => {
    const api = await provisionUser(unique('tagwrites'));
    const firstTagId = await api.content.createTag(unique('first').username);
    const secondTagId = await api.content.createTag(unique('second').username);

    for (let round = 0; round < ROUNDS; round++) {
      const imageId = await api.content.uploadImage();

      const [first, second] = await Promise.all([
        send(api.token, 'POST', `/api/images/${imageId}/tags/${firstTagId}`),
        send(api.token, 'POST', `/api/images/${imageId}/tags/${secondTagId}`),
      ]);

      expect([204, 409], `round ${round} first`).toContain(first.status);
      expect([204, 409], `round ${round} second`).toContain(second.status);

      const acknowledged: string[] = [];
      if (first.status === 204) {
        acknowledged.push(firstTagId);
      }
      if (second.status === 204) {
        acknowledged.push(secondTagId);
      }

      expect(
        (await listTagIds(api.token, imageId)).sort(),
        `round ${round}`,
      ).toEqual(acknowledged.sort());
    }
  });
});
