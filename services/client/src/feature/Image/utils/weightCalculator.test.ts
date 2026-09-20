import { describe, expect, it } from 'vitest';

import { calculateImageCardWeight } from './calculateImageCardWeight';
import { getAspectRatio } from './weightCalculator';

describe('weightCalculator', () => {
  describe('id-only items', () => {
    it('U1 — returns finite weight for id-only item without throwing', () => {
      const item = { id: 'bookmark-item-1' };

      const weight = calculateImageCardWeight(item);

      expect(Number.isFinite(weight)).toBe(true);
      expect(weight).toBeGreaterThan(0);
    });

    it('U2 — id-only weight equals neutral aspect-ratio weight', () => {
      const idOnlyItem = { id: 'x' };
      const emptyMetadataItem = { id: 'y', metadata: {} };

      const idOnlyWeight = calculateImageCardWeight(idOnlyItem);
      const emptyMetadataWeight = calculateImageCardWeight(emptyMetadataItem);

      expect(idOnlyWeight).toBe(emptyMetadataWeight);
    });

    it('U3 — no-throw across degenerate shapes', () => {
      const degenerateShapes = [
        { id: 'a' },
        { id: 'b', metadata: {} },
        { id: 'c', attributes: {} },
        { id: 'd', metadata: {}, attributes: {} },
        { id: 'e', metadata: { width: 0, height: 0 } },
      ];

      for (const shape of degenerateShapes) {
        const weight = calculateImageCardWeight(shape);
        expect(Number.isFinite(weight)).toBe(true);
        expect(weight).toBeGreaterThan(0);
      }
    });

    it('U4 — getAspectRatio tolerates missing metadata', () => {
      const idOnlyItem = { id: 'a' };
      const emptyMetadataItem = { id: 'a', metadata: {} };

      const idOnlyRatio = getAspectRatio(idOnlyItem);
      const emptyMetadataRatio = getAspectRatio(emptyMetadataItem);

      expect(idOnlyRatio).toBe(emptyMetadataRatio);
    });
  });

  describe('regression guard', () => {
    it('U5 — fully populated item keeps exact weight', () => {
      const fullItem = {
        id: 'test-1',
        url: 'https://example.com/image.jpg',
        owner: {
          id: 'owner-1',
          email: 'test@example.com',
          displayName: 'Test User',
        },
        attributes: {
          fileName: 'image.jpg',
          description: 'A'.repeat(100),
          isPublic: true,
          createdAt: {
            formattedDate: '2026-09-20',
            timestamp: 1234567890,
          },
          views: 42,
        },
        metadata: {
          width: 800,
          height: 600,
          size: 1,
          mimeType: 'image/png',
        },
        license: {
          id: 'cc0',
          title: 'CC0',
          name: 'Public Domain',
          description: 'No rights reserved',
          url: null,
          isCreativeCommons: false,
        },
        bookmarkCount: 5,
      };

      const weight = calculateImageCardWeight(fullItem);

      expect(weight).toBeCloseTo(0.85, 10);
    });

    it('U6 — tall and wide items stay ordered', () => {
      const tallItem = {
        id: 'tall',
        metadata: { width: 400, height: 1200, size: 1, mimeType: 'image/png' },
      };

      const wideItem = {
        id: 'wide',
        metadata: { width: 1200, height: 400, size: 1, mimeType: 'image/png' },
      };

      const neutralItem = { id: 'neutral' };

      const tallWeight = calculateImageCardWeight(tallItem);
      const wideWeight = calculateImageCardWeight(wideItem);
      const neutralWeight = calculateImageCardWeight(neutralItem);

      expect(tallWeight).toBeGreaterThan(neutralWeight);
      expect(neutralWeight).toBeGreaterThan(wideWeight);
    });

    it('U7 — description weight still applies', () => {
      const baseItem = {
        id: 'base',
        metadata: { width: 800, height: 600, size: 1, mimeType: 'image/png' },
      };

      const describedItem = {
        ...baseItem,
        id: 'described',
        attributes: { description: 'A'.repeat(100) },
      };

      const baseWeight = calculateImageCardWeight(baseItem);
      const describedWeight = calculateImageCardWeight(describedItem);

      expect(describedWeight).toBeGreaterThan(baseWeight);
    });
  });
});
