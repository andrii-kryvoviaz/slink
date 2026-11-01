<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Lock\LockFactory;

final class BatchCollectionLockTest extends ConcurrentRequestTestCase {
  private string $ownerToken = '';

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('batch-lock-owner@local.test', 'batchlockowner', self::PASSWORD);
    $this->ownerToken = $this->login('batchlockowner', self::PASSWORD);
  }

  private function isItemsLockHeldByAnotherRequest(string $collectionId): bool {
    /** @var LockFactory $lockFactory */
    $lockFactory = static::getContainer()->get(LockFactory::class);
    $competing = $lockFactory->createLock('collection-items-' . $collectionId);

    if (!$competing->acquire()) {
      return true;
    }

    $competing->release();

    return false;
  }

  /**
   * @param list<string> $collectionIds
   */
  private function reassign(string $imageId, array $collectionIds): int {
    return $this->sendJson('PUT', '/api/images/batch', $this->ownerToken, [
      'assignments' => [$imageId => ['collectionIds' => $collectionIds]],
    ]);
  }

  private function itemCount(string $collectionId): int {
    return $this->countRowsByColumn('collection_item', 'collection_id', $collectionId);
  }

  #[Test]
  public function aBatchAddingAnImageToACollectionHoldsTheItemsLockWhileItWrites(): void {
    $imageId = $this->uploadImage($this->ownerToken, false);
    $collectionId = $this->createCollection($this->ownerToken);
    $heldWhileWriting = null;

    $this->beforeTheNextFlush(function () use (&$heldWhileWriting, $collectionId): void {
      $heldWhileWriting = $this->isItemsLockHeldByAnotherRequest($collectionId);
    });

    self::assertSame(200, $this->reassign($imageId, [$collectionId]));

    self::assertTrue($heldWhileWriting, 'The batch added the image without holding the collection items lock.');
    self::assertSame(1, $this->itemCount($collectionId));
    self::assertFalse($this->isItemsLockHeldByAnotherRequest($collectionId));
  }

  #[Test]
  public function aBatchRemovingAnImageFromACollectionHoldsTheItemsLockWhileItWrites(): void {
    $imageId = $this->uploadImage($this->ownerToken, false);
    $collectionId = $this->createCollection($this->ownerToken);
    $this->addImageToCollection($this->ownerToken, $collectionId, $imageId);
    $heldWhileWriting = null;

    $this->beforeTheNextFlush(function () use (&$heldWhileWriting, $collectionId): void {
      $heldWhileWriting = $this->isItemsLockHeldByAnotherRequest($collectionId);
    });

    self::assertSame(200, $this->reassign($imageId, []));

    self::assertTrue($heldWhileWriting, 'The batch removed the image without holding the collection items lock.');
    self::assertSame(0, $this->itemCount($collectionId));
    self::assertFalse($this->isItemsLockHeldByAnotherRequest($collectionId));
  }
}
