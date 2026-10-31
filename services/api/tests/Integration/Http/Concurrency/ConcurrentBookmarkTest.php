<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\Test;

final class ConcurrentBookmarkTest extends ConcurrentRequestTestCase {
  private const int READERS = 6;

  private string $ownerToken = '';

  /** @var list<string> */
  private array $readerTokens = [];

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('bookmark-race-owner@local.test', 'bookmarkraceowner', self::PASSWORD);
    $this->ownerToken = $this->login('bookmarkraceowner', self::PASSWORD);

    for ($reader = 1; $reader <= self::READERS; $reader++) {
      $this->createUser("bookmark-race-reader{$reader}@local.test", "bookmarkracereader{$reader}", self::PASSWORD);
      $this->readerTokens[] = $this->login("bookmarkracereader{$reader}", self::PASSWORD);
    }
  }

  private function bookmarkPath(string $imageId): string {
    return \sprintf('/api/image/%s/bookmark', $imageId);
  }

  private function bookmarkFromAnotherKernel(string $imageId, string $token): int {
    return $this->requestFromAnotherKernel('POST', $this->bookmarkPath($imageId), $token)['status'];
  }

  private function editFromAnotherKernel(string $imageId): int {
    return $this->requestFromAnotherKernel('PATCH', '/api/image/' . $imageId, $this->ownerToken, [
      'isPublic' => true,
      'description' => 'winner caption',
    ])['status'];
  }

  private function bookmarkCountOf(string $imageId): int {
    self::assertSame(200, $this->apiRequest('GET', '/api/image/' . $imageId . '/public', $this->ownerToken));

    /** @var array{data?: array{bookmarkCount?: int}, bookmarkCount?: int} $payload */
    $payload = $this->responsePayload();

    return $payload['data']['bookmarkCount'] ?? $payload['bookmarkCount'] ?? -1;
  }

  #[Test]
  public function sixDifferentUsersBookmarkingOneImageAtOnceAllSucceedAndEveryRowIsStored(): void {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $overlapping = [];

    $this->beforeTheNextFlush(function () use (&$overlapping, $imageId): void {
      foreach (\array_slice($this->readerTokens, 1) as $token) {
        $overlapping[] = $this->bookmarkFromAnotherKernel($imageId, $token);
      }
    });

    self::assertSame(201, $this->apiRequest('POST', $this->bookmarkPath($imageId), $this->readerTokens[0]));

    self::assertSame(\array_fill(0, self::READERS - 1, 201), $overlapping);
    self::assertSame(self::READERS, $this->countRowsByColumn('bookmark', 'image_id', $imageId));
    self::assertSame(self::READERS, $this->bookmarkCountOf($imageId));
  }

  #[Test]
  public function aBookmarkOverlappingAnImageEditByTheOwnerStoresBoth(): void {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $edit = null;

    $this->beforeTheNextFlush(function () use (&$edit, $imageId): void {
      $edit = $this->editFromAnotherKernel($imageId);
    });

    self::assertSame(201, $this->apiRequest('POST', $this->bookmarkPath($imageId), $this->readerTokens[0]));

    self::assertContains($edit, [200, 204]);
    self::assertSame('winner caption', $this->imageColumn($imageId, 'description'));
    self::assertSame(1, $this->countRowsByColumn('bookmark', 'image_id', $imageId));
  }

  #[Test]
  public function anImageEditByTheOwnerOverlappingABookmarkStoresBoth(): void {
    $imageId = $this->uploadImage($this->ownerToken, true);
    $bookmark = null;

    $this->beforeTheNextFlush(function () use (&$bookmark, $imageId): void {
      $bookmark = $this->bookmarkFromAnotherKernel($imageId, $this->readerTokens[0]);
    });

    self::assertContains($this->sendJson('PATCH', '/api/image/' . $imageId, $this->ownerToken, [
      'isPublic' => true,
      'description' => 'owner caption',
    ]), [200, 204]);

    self::assertSame(201, $bookmark);
    self::assertSame('owner caption', $this->imageColumn($imageId, 'description'));
    self::assertSame(1, $this->countRowsByColumn('bookmark', 'image_id', $imageId));
  }
}
