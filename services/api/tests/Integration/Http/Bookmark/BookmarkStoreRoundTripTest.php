<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Bookmark;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Bookmark\Domain\Bookmark;
use Slink\Bookmark\Domain\Repository\BookmarkStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<Bookmark>
 */
#[Group('sc-1176')]
final class BookmarkStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
  private const string STORED_TIMESTAMP = '2021-02-03 04:05:06';

  private string $adminToken = '';
  private string $ownerToken = '';
  private string $readerId = '';
  private string $readerToken = '';
  private string $imageId = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->bootParties();
    $this->bookmark();

    return ID::fromString($this->bookmarkId());
  }

  #[\Override]
  protected function fetch(ID $id): Bookmark {
    /** @var BookmarkStoreRepositoryInterface $store */
    $store = static::getContainer()->get(BookmarkStoreRepositoryInterface::class);

    return $store->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'imageId' => $aggregate->getImageId()->toString(),
      'userId' => $aggregate->getUserId()->toString(),
      'createdAt' => $aggregate->getCreatedAt()->format(self::TIMESTAMP_FORMAT),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "bookmark" WHERE uuid = ?', [$id->toString()]);
    self::assertIsArray($row);

    return [
      'imageId' => $row['image_id'],
      'userId' => $row['user_id'],
      'createdAt' => $this->secondsOf($row['created_at']),
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'bookmark';
  }

  #[Test]
  public function addingAndRemovingABookmarkChangesItsRowAndAppendsNothing(): void {
    $this->bootParties();
    $logBefore = $this->eventLogSize();

    $this->bookmark();
    self::assertSame(1, $this->countRowsByColumn('bookmark', 'image_id', $this->imageId));

    $this->assertRemoved($this->removeBookmark());
    self::assertSame(0, $this->countRowsByColumn('bookmark', 'image_id', $this->imageId));

    self::assertSame($logBefore, $this->eventLogSize());
  }

  #[Test]
  public function reloadedBookmarkTakesItsCreationTimeFromTheRow(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement(
      'UPDATE "bookmark" SET created_at = ? WHERE uuid = ?',
      [self::STORED_TIMESTAMP, $id->toString()],
    );

    $bookmark = $this->reload($id);

    self::assertSame(self::STORED_TIMESTAMP, $bookmark->getCreatedAt()->format(self::TIMESTAMP_FORMAT));
    self::assertSame($this->imageId, $bookmark->getImageId()->toString());
    self::assertSame($this->readerId, $bookmark->getUserId()->toString());
  }

  #[Test]
  public function aRemovedBookmarkIsNotFound(): void {
    $id = $this->createAggregate();
    $this->assertRemoved($this->removeBookmark());

    $this->expectException(NotFoundException::class);

    $this->reload($id);
  }

  #[Test]
  public function aBookmarkPurgedWithItsImageIsNotFound(): void {
    $id = $this->createAggregate();
    self::assertContains(
      $this->apiRequest('DELETE', \sprintf('/api/image/%s', $this->imageId), $this->ownerToken),
      [200, 204],
    );
    self::assertSame(0, $this->countRowsByColumn('bookmark', 'uuid', $id->toString()));

    $this->expectException(NotFoundException::class);

    $this->reload($id);
  }

  #[Test]
  public function aBookmarkPurgedWithItsUserIsNotFound(): void {
    $id = $this->createAggregate();
    self::assertSame(204, $this->apiRequest('DELETE', '/api/user/' . $this->readerId, $this->adminToken));
    self::assertSame(0, $this->countRowsByColumn('bookmark', 'uuid', $id->toString()));

    $this->expectException(NotFoundException::class);

    $this->reload($id);
  }

  #[Test]
  public function removingABookmarkTwiceAnswersSuccessBothTimes(): void {
    $this->createAggregate();

    $this->assertRemoved($this->removeBookmark());
    $this->assertRemoved($this->removeBookmark());

    self::assertSame(0, $this->countRowsByColumn('bookmark', 'image_id', $this->imageId));
  }

  #[Test]
  public function bookmarkingAgainAfterRemovalLoadsTheNewBookmark(): void {
    $firstId = $this->createAggregate();
    $this->assertRemoved($this->removeBookmark());

    $this->bookmark();

    $secondId = ID::fromString($this->bookmarkId());
    self::assertFalse($firstId->equals($secondId));
    self::assertSame($this->storedState($secondId), $this->aggregateState($this->reload($secondId)));
  }

  private function bootParties(): void {
    $this->adminToken = $this->bootAdmin();
    $this->createUser('bookmark-store-owner@local.test', 'bookmarkstoreowner', self::PASSWORD);
    $this->readerId = $this->createUser('bookmark-store-reader@local.test', 'bookmarkstorereader', self::PASSWORD);
    $this->ownerToken = $this->login('bookmarkstoreowner', self::PASSWORD);
    $this->readerToken = $this->login('bookmarkstorereader', self::PASSWORD);
    $this->imageId = $this->uploadImage($this->ownerToken, true);
  }

  private function bookmark(): void {
    self::assertContains(
      $this->apiRequest('POST', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->readerToken),
      [200, 201],
    );
  }

  private function removeBookmark(): int {
    return $this->apiRequest('DELETE', \sprintf('/api/image/%s/bookmark', $this->imageId), $this->readerToken);
  }

  private function assertRemoved(int $status): void {
    self::assertContains($status, [200, 204], (string) $this->client->getResponse()->getContent());
  }

  private function bookmarkId(): string {
    $id = $this->readModel()->fetchOne(
      'SELECT uuid FROM "bookmark" WHERE image_id = ? AND user_id = ?',
      [$this->imageId, $this->readerId],
    );
    self::assertIsString($id);

    return $id;
  }
}
