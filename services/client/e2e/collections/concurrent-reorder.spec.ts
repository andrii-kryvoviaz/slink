import { expect, test } from '../fixtures/auth.fixture';
import { unique } from '../helpers/accounts';
import { provisionUser } from '../helpers/provisioning';

const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8180';
const ITEM_COUNT = 6;
const ROUNDS = 12;

type CollectionItem = { itemId: string; position: number };

function rotate(ids: string[], by: number): string[] {
  const offset = by % ids.length;

  return [...ids.slice(offset), ...ids.slice(0, offset)];
}

async function reorder(
  token: string,
  collectionId: string,
  orderedItemIds: string[],
): Promise<number> {
  const response = await fetch(`${API_URL}/api/collection/items/order`, {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: JSON.stringify({ collectionId, orderedItemIds }),
  });

  return response.status;
}

async function listItems(
  token: string,
  collectionId: string,
): Promise<CollectionItem[]> {
  const response = await fetch(
    `${API_URL}/api/collection/${collectionId}/items`,
    { headers: { Authorization: `Bearer ${token}` } },
  );
  const body = await response.json();

  return (body.data ?? body) as CollectionItem[];
}

test.describe('Concurrent collection reorder', () => {
  test('parallel reorders leave distinct positions that match one of the requested orders', async () => {
    const api = await provisionUser(unique('reorder'));
    const collectionId = await api.content.createCollection({
      name: 'concurrent-reorder',
    });

    const itemIds: string[] = [];
    for (let index = 0; index < ITEM_COUNT; index++) {
      const imageId = await api.content.uploadImage();
      await api.content.addImageToCollection(collectionId, imageId);
      itemIds.push(imageId);
    }

    for (let round = 0; round < ROUNDS; round++) {
      const first = rotate(itemIds, round + 1);
      const second = rotate([...itemIds].reverse(), round + 3);

      const statuses = await Promise.all([
        reorder(api.token, collectionId, first),
        reorder(api.token, collectionId, second),
      ]);

      expect(statuses, `round ${round}`).toEqual([204, 204]);

      const items = (await listItems(api.token, collectionId)).sort(
        (left, right) => left.position - right.position,
      );
      const positions = items.map((item) => item.position);

      expect(new Set(positions).size, `round ${round}`).toBe(ITEM_COUNT);
      expect([first, second], `round ${round}`).toContainEqual(
        items.map((item) => item.itemId),
      );
    }
  });
});
