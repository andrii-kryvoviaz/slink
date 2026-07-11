<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class BookmarkersCursorPaginationTest extends HttpTestCase {
  private const int BOOKMARKER_COUNT = 15;
  private const int PAGE_LIMIT = 12;

  #[Test]
  public function paginatesSameTimestampBookmarkersWithoutOverlapOrGaps(): void {
    $this->createUser('bookmarkers-owner@local.test', 'bookmarkersowner', self::PASSWORD);
    $ownerToken = $this->login('bookmarkersowner', self::PASSWORD);
    $imageId = $this->uploadImage($ownerToken, true);

    for ($i = 1; $i <= self::BOOKMARKER_COUNT; $i++) {
      $this->createUser(\sprintf('bookmarker-%02d@local.test', $i), \sprintf('bookmarker%02d', $i), self::PASSWORD);
      $bookmarkerToken = $this->login(\sprintf('bookmarker%02d', $i), self::PASSWORD);

      self::assertContains(
        $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $bookmarkerToken),
        [200, 201],
        \sprintf('Bookmarker %d failed to bookmark.', $i),
      );
    }

    $this->normalizeBookmarkTimestamps();

    $firstPage = $this->fetchBookmarkersPage($ownerToken, $imageId);
    $firstPageRepeat = $this->fetchBookmarkersPage($ownerToken, $imageId);

    self::assertSame($firstPage['ids'], $firstPageRepeat['ids'], 'Page 1 ordering must be deterministic.');
    self::assertCount(self::PAGE_LIMIT, $firstPage['ids']);
    self::assertNotNull($firstPage['nextCursor'], 'Page 1 must expose a next cursor.');

    $secondPage = $this->fetchBookmarkersPage($ownerToken, $imageId, $firstPage['nextCursor']);

    self::assertSame(
      [],
      \array_values(\array_intersect($firstPage['ids'], $secondPage['ids'])),
      'Pages must not overlap.',
    );

    $union = \array_unique(\array_merge($firstPage['ids'], $secondPage['ids']));
    self::assertCount(self::BOOKMARKER_COUNT, $union, 'Both pages together must cover every bookmarker exactly once.');
  }

  private function normalizeBookmarkTimestamps(): void {
    /** @var EntityManagerInterface $em */
    $em = static::getContainer()->get(EntityManagerInterface::class);
    $em->getConnection()->executeStatement('UPDATE "bookmark" SET created_at = :ts', ['ts' => '2026-01-01 10:00:00']);
    $em->clear();
  }

  /**
   * @return array{ids: array<int, string>, nextCursor: string|null}
   */
  private function fetchBookmarkersPage(string $token, string $imageId, ?string $cursor = null): array {
    $path = \sprintf('/api/image/%s/bookmarkers?limit=%d', $imageId, self::PAGE_LIMIT);

    if ($cursor !== null) {
      $path .= '&cursor=' . \urlencode($cursor);
    }

    $status = $this->apiRequest('GET', $path, $token);
    $response = $this->client->getResponse();
    self::assertSame(200, $status, 'Fetch bookmarkers failed: ' . (string) $response->getContent());

    /** @var array{data?: array<int, array{id?: string}>, meta?: array{nextCursor?: string}} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      if (\is_string($item['id'] ?? null)) {
        $ids[] = $item['id'];
      }
    }

    return [
      'ids' => $ids,
      'nextCursor' => $payload['meta']['nextCursor'] ?? null,
    ];
  }
}
