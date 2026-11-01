<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Concurrency;

use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Application\Lock\ResourceLock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class CollectionItemsLockTimeoutTest extends ConcurrentRequestTestCase {
  private string $ownerToken = '';
  private string $collectionId = '';
  private string $imageId = '';
  private ?SharedLockInterface $heldLock = null;

  protected function setUp(): void {
    parent::setUp();

    $this->createUser('lock-timeout-owner@local.test', 'locktimeoutowner', self::PASSWORD);
    $this->ownerToken = $this->login('locktimeoutowner', self::PASSWORD);
    $this->collectionId = $this->createCollection($this->ownerToken);
    $this->imageId = $this->uploadImage($this->ownerToken, false);

    /** @var LockFactory $lockFactory */
    $lockFactory = static::getContainer()->get(LockFactory::class);
    static::getContainer()->set(ResourceLock::class, new ResourceLock($lockFactory, 0.2));
    $this->heldLock = $lockFactory->createLock('collection-items-' . $this->collectionId);
  }

  protected function tearDown(): void {
    $this->heldLock?->release();

    parent::tearDown();
  }

  private function addItem(): int {
    return $this->apiRequest(
      'POST',
      \sprintf('/api/collection/%s/items/%s', $this->collectionId, $this->imageId),
      $this->ownerToken,
    );
  }

  #[Test]
  public function anAddWaitingTooLongForTheItemsLockAnswersConflictAndStoresNothing(): void {
    self::assertTrue($this->heldLock?->acquire());

    $status = $this->addItem();

    self::assertSame(409, $status, (string) $this->client->getResponse()->getContent());
    self::assertSame(['error' => ['title' => 'Conflict', 'message' => 'Conflict']], $this->responsePayload());
    self::assertSame(0, $this->countRowsByColumn('collection_item', 'collection_id', $this->collectionId));
  }

  #[Test]
  public function anAddSucceedsOnceTheItemsLockIsReleased(): void {
    self::assertTrue($this->heldLock?->acquire());
    self::assertSame(409, $this->addItem());

    $this->heldLock->release();

    self::assertContains($this->addItem(), [200, 201, 204]);
    self::assertSame(1, $this->countRowsByColumn('collection_item', 'collection_id', $this->collectionId));
  }
}
