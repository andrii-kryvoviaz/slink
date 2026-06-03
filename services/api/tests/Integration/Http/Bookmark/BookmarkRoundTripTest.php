<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class BookmarkRoundTripTest extends HttpTestCase {
  private function addBookmark(string $token, string $imageId): int {
    return $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $token);
  }

  private function removeBookmark(string $token, string $imageId): int {
    return $this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $imageId), $token);
  }

  private function bookmarkStatus(string $token, string $imageId): bool {
    $this->apiRequest('GET', \sprintf('/api/image/%s/bookmark/status', $imageId), $token);

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'Bookmark status failed: ' . (string) $response->getContent());

    /** @var array{isBookmarked?: bool} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    return $payload['isBookmarked'] ?? false;
  }

  /**
   * @return array<int, string>
   */
  private function listBookmarkedImageIds(string $token): array {
    $this->apiRequest('GET', '/api/bookmarks', $token);

    $response = $this->client->getResponse();
    self::assertSame(200, $response->getStatusCode(), 'List bookmarks failed: ' . (string) $response->getContent());

    /** @var array{data?: array<int, array{image?: array{id?: string}}>} $payload */
    $payload = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

    $ids = [];
    foreach ($payload['data'] ?? [] as $item) {
      $imageId = $item['image']['id'] ?? null;
      if (\is_string($imageId)) {
        $ids[] = $imageId;
      }
    }

    return $ids;
  }

  #[Test]
  public function authenticatedUserCanRoundTripBookmark(): void {
    $this->createUser('bookmark-owner@local.test', 'bookmarkowner', self::PASSWORD);
    $this->createUser('bookmark-reader@local.test', 'bookmarkreader', self::PASSWORD);
    $ownerToken = $this->login('bookmarkowner', self::PASSWORD);
    $token = $this->login('bookmarkreader', self::PASSWORD);
    $imageId = $this->uploadImage($ownerToken, true);

    $addStatus = $this->addBookmark($token, $imageId);
    self::assertContains($addStatus, [200, 201], 'Add bookmark should return 200/201.');

    self::assertTrue(
      $this->bookmarkStatus($token, $imageId),
      'Status must report the image as bookmarked after add.',
    );

    self::assertContains(
      $imageId,
      $this->listBookmarkedImageIds($token),
      'Bookmarked image must appear in /api/bookmarks.',
    );

    self::assertContains(
      $this->removeBookmark($token, $imageId),
      [200, 204],
      'Remove bookmark should return 200/204.',
    );

    self::assertFalse(
      $this->bookmarkStatus($token, $imageId),
      'Status must report the image as not bookmarked after remove.',
    );

    self::assertNotContains(
      $imageId,
      $this->listBookmarkedImageIds($token),
      'Removed image must no longer appear in /api/bookmarks.',
    );
  }

  #[Test]
  public function addBookmarkResponseReportsBookmarked(): void {
    $this->createUser('bookmark-payload-owner@local.test', 'bookmarkpayloadowner', self::PASSWORD);
    $this->createUser('bookmark-payload@local.test', 'bookmarkpayload', self::PASSWORD);
    $ownerToken = $this->login('bookmarkpayloadowner', self::PASSWORD);
    $token = $this->login('bookmarkpayload', self::PASSWORD);
    $imageId = $this->uploadImage($ownerToken, true);

    $this->addBookmark($token, $imageId);

    /** @var array{isBookmarked?: bool} $payload */
    $payload = \json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

    self::assertTrue($payload['isBookmarked'] ?? false, 'Add bookmark response must report isBookmarked=true.');
  }

  #[Test]
  public function anonymousCannotAccessBookmarks(): void {
    self::assertSame(401, $this->apiRequest('GET', '/api/bookmarks', null));
  }
}
