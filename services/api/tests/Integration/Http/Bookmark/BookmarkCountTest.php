<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use PHPUnit\Framework\Attributes\Test;
use Tests\Integration\Http\HttpTestCase;

final class BookmarkCountTest extends HttpTestCase {
  private string $adminToken = '';
  private string $ownerToken = '';
  private string $popularImageId = '';
  private string $singleImageId = '';
  private string $lonelyImageId = '';
  private string $firstReaderId = '';
  private string $firstReaderToken = '';
  private string $secondReaderToken = '';
  private string $thirdReaderToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->adminToken = $this->bootAdmin();
    $this->createUser('count-owner@local.test', 'countowner', self::PASSWORD);
    $this->firstReaderId = $this->createUser('count-first@local.test', 'countfirst', self::PASSWORD);
    $this->createUser('count-second@local.test', 'countsecond', self::PASSWORD);
    $this->createUser('count-third@local.test', 'countthird', self::PASSWORD);
    $this->ownerToken = $this->login('countowner', self::PASSWORD);
    $this->firstReaderToken = $this->login('countfirst', self::PASSWORD);
    $this->secondReaderToken = $this->login('countsecond', self::PASSWORD);
    $this->thirdReaderToken = $this->login('countthird', self::PASSWORD);

    $this->popularImageId = $this->uploadImage($this->ownerToken, true);
    $this->singleImageId = $this->uploadImage($this->ownerToken, true);
    $this->lonelyImageId = $this->uploadImage($this->ownerToken, true);

    $this->bookmark($this->firstReaderToken, $this->popularImageId);
    $this->bookmark($this->secondReaderToken, $this->popularImageId);
    $this->bookmark($this->firstReaderToken, $this->singleImageId);
  }

  private function bookmark(string $token, string $imageId): void {
    self::assertSame(201, $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $imageId), $token));
  }

  private function unbookmark(string $token, string $imageId): void {
    self::assertContains($this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $imageId), $token), [200, 204]);
  }

  /**
   * @return array<string, mixed>
   */
  private function read(string $path, ?string $token): array {
    $status = $this->apiRequest('GET', $path, $token);
    self::assertSame(200, $status, $path . ': ' . (string) $this->client->getResponse()->getContent());

    return $this->responsePayload();
  }

  /**
   * @param array<array-key, mixed> $rows
   * @return array<string, int>
   */
  private function countsById(array $rows, string ...$ids): array {
    $counts = [];

    foreach ($rows as $row) {
      self::assertIsArray($row);
      $image = $row['item'] ?? $row['image'] ?? $row;
      self::assertIsArray($image);
      self::assertArrayHasKey('bookmarkCount', $image);
      $counts[(string) $image['id']] = $image['bookmarkCount'];
    }

    return \array_intersect_key($counts, \array_flip($ids));
  }

  /**
   * @return array<string, int>
   */
  private function expectedCounts(int $popular, int $single, int $lonely): array {
    return [
      $this->popularImageId => $popular,
      $this->singleImageId => $single,
      $this->lonelyImageId => $lonely,
    ];
  }

  private function detailCount(string $path, ?string $token): mixed {
    $payload = $this->read($path, $token);
    $image = $payload['data'] ?? $payload;
    self::assertIsArray($image);
    self::assertArrayHasKey('bookmarkCount', $image);

    return $image['bookmarkCount'];
  }

  /**
   * @return list<string>
   */
  private function ids(): array {
    return [$this->popularImageId, $this->singleImageId, $this->lonelyImageId];
  }

  private function publishedCollectionHolding(): string {
    $collectionId = $this->createCollection($this->ownerToken);

    foreach ($this->ids() as $imageId) {
      $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);
    }

    $this->publishShare($this->ownerToken, $this->createCollectionShare($this->ownerToken, $collectionId));

    return $collectionId;
  }

  #[Test]
  public function theImageListCarriesEachImagesOwnCount(): void {
    $rows = $this->read('/api/images', $this->thirdReaderToken)['data'];

    self::assertEquals($this->expectedCounts(2, 1, 0), $this->countsById($rows, ...$this->ids()));
  }

  #[Test]
  public function theImageListCarriesTheCountsForAnUnauthenticatedViewer(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);

    $rows = $this->read('/api/images', null)['data'];

    self::assertEquals($this->expectedCounts(2, 1, 0), $this->countsById($rows, ...$this->ids()));
  }

  #[Test]
  public function theImageListMarksOnlyTheViewersOwnBookmarks(): void {
    $rows = $this->read('/api/images', $this->secondReaderToken)['data'];
    $marked = [];

    foreach ($rows as $row) {
      $marked[$row['id']] = $row['isBookmarked'];
    }

    self::assertTrue($marked[$this->popularImageId]);
    self::assertFalse($marked[$this->singleImageId]);
    self::assertFalse($marked[$this->lonelyImageId]);
  }

  #[Test]
  public function thePublicDetailCarriesTheCount(): void {
    self::assertSame(2, $this->detailCount(\sprintf('/api/image/%s/public', $this->popularImageId), $this->ownerToken));
    self::assertSame(0, $this->detailCount(\sprintf('/api/image/%s/public', $this->lonelyImageId), $this->ownerToken));
  }

  #[Test]
  public function theOwnerDetailCarriesTheCount(): void {
    self::assertSame(2, $this->detailCount(\sprintf('/api/image/%s/detail', $this->popularImageId), $this->ownerToken));
    self::assertSame(0, $this->detailCount(\sprintf('/api/image/%s/detail', $this->lonelyImageId), $this->ownerToken));
  }

  #[Test]
  public function theListByIdCarriesEachImagesOwnCount(): void {
    $query = \http_build_query(['uuid' => $this->ids()]);

    $rows = $this->read('/api/images/by-id?' . $query, $this->ownerToken)['data'];

    self::assertEquals($this->expectedCounts(2, 1, 0), $this->countsById($rows, ...$this->ids()));
  }

  #[Test]
  public function theBookmarkListCarriesEachImagesOwnCount(): void {
    $rows = $this->read('/api/bookmarks', $this->firstReaderToken)['data'];

    self::assertEquals(
      [$this->popularImageId => 2, $this->singleImageId => 1],
      $this->countsById($rows, $this->popularImageId, $this->singleImageId),
    );
  }

  #[Test]
  public function theCollectionItemsCarryEachImagesOwnCountForTheOwnerAndAnUnauthenticatedViewer(): void {
    $this->setAccessSettings(['allowUnauthenticatedAccess' => true]);
    $path = \sprintf('/api/collection/%s/items', $this->publishedCollectionHolding());

    $ownerRows = $this->read($path, $this->ownerToken)['data'];
    $anonymousRows = $this->read($path, null)['data'];

    self::assertEquals($this->expectedCounts(2, 1, 0), $this->countsById($ownerRows, ...$this->ids()));
    self::assertEquals($this->expectedCounts(2, 1, 0), $this->countsById($anonymousRows, ...$this->ids()));
  }

  #[Test]
  public function aRemovedBookmarkNoLongerCounts(): void {
    $this->unbookmark($this->firstReaderToken, $this->popularImageId);

    $rows = $this->read('/api/images', $this->thirdReaderToken)['data'];

    self::assertEquals($this->expectedCounts(1, 1, 0), $this->countsById($rows, ...$this->ids()));
  }

  #[Test]
  public function aPurgedUsersBookmarksNoLongerCount(): void {
    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->firstReaderId, $this->adminToken));

    $rows = $this->read('/api/images', $this->thirdReaderToken)['data'];

    self::assertEquals($this->expectedCounts(1, 0, 0), $this->countsById($rows, ...$this->ids()));
    self::assertSame(1, $this->detailCount(\sprintf('/api/image/%s/public', $this->popularImageId), $this->ownerToken));
  }
}
