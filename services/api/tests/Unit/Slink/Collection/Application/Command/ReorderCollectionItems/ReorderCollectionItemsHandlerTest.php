<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Command\ReorderCollectionItems;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Command\ReorderCollectionItems\ReorderCollectionItemsCommand;
use Slink\Collection\Application\Command\ReorderCollectionItems\ReorderCollectionItemsHandler;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ReorderCollectionItemsHandlerTest extends TestCase {

  private function createLockFactory(): LockFactory {
    return new LockFactory(new InMemoryStore());
  }

  private function isLockedByAnotherRequest(LockFactory $lockFactory, ID $collectionId): bool {
    $competing = $lockFactory->createLock('collection-items-' . $collectionId->toString());

    if (!$competing->acquire()) {
      return true;
    }

    $competing->release();

    return false;
  }

  private function createCollection(ID $collectionId, ID $ownerId): Collection {
    $collection = Collection::create(
      $collectionId,
      $ownerId,
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test')
    );
    $collection->releaseEvents();

    return $collection;
  }

  #[Test]
  public function itReordersCollectionItems(): void {
    $userId = ID::generate();
    $collectionId = ID::generate();
    $item1Id = ID::generate();
    $item2Id = ID::generate();
    $item3Id = ID::generate();

    $collection = Collection::create(
      $collectionId,
      $userId,
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test')
    );
    $collection->addItem($item1Id, ItemType::Image);
    $collection->addItem($item2Id, ItemType::Image);
    $collection->addItem($item3Id, ItemType::Image);
    $collection->releaseEvents();

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->expects($this->once())
      ->method('get')
      ->with($this->callback(fn($id) => $id->equals($collectionId)))
      ->willReturn($collection);

    $collectionStore
      ->expects($this->once())
      ->method('store')
      ->with($collection);

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $handler = new ReorderCollectionItemsHandler($collectionStore, $access, $this->createLockFactory());

    $newOrder = [$item3Id->toString(), $item1Id->toString(), $item2Id->toString()];
    $command = new ReorderCollectionItemsCommand($collectionId->toString(), $newOrder);

    $handler($command, $userId->toString());

    $events = $collection->releaseEvents();
    $this->assertCount(1, $events);
  }

  #[Test]
  public function itThrowsExceptionWhenUserDoesNotOwnCollection(): void {
    $this->expectException(CollectionAccessDeniedException::class);

    $ownerId = ID::generate();
    $differentUserId = ID::generate();
    $collectionId = ID::generate();

    $collection = Collection::create(
      $collectionId,
      $ownerId,
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test')
    );

    $collectionStore = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturn($collection);

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(false);

    $handler = new ReorderCollectionItemsHandler($collectionStore, $access, $this->createLockFactory());

    $command = new ReorderCollectionItemsCommand($collectionId->toString(), []);

    $handler($command, $differentUserId->toString());
  }

  #[Test]
  public function itHoldsTheCollectionItemsLockFromLoadThroughStore(): void {
    $ownerId = ID::generate();
    $collectionId = ID::generate();
    $firstItemId = ID::generate();
    $secondItemId = ID::generate();
    $lockFactory = $this->createLockFactory();
    $collection = $this->createCollection($collectionId, $ownerId);
    $collection->addItem($firstItemId, ItemType::Image);
    $collection->addItem($secondItemId, ItemType::Image);

    $lockedWhile = [];

    $collectionStore = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturnCallback(function () use (&$lockedWhile, $lockFactory, $collectionId, $collection): Collection {
        $lockedWhile['load'] = $this->isLockedByAnotherRequest($lockFactory, $collectionId);

        return $collection;
      });
    $collectionStore
      ->method('store')
      ->willReturnCallback(function () use (&$lockedWhile, $lockFactory, $collectionId): void {
        $lockedWhile['store'] = $this->isLockedByAnotherRequest($lockFactory, $collectionId);
      });

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $handler = new ReorderCollectionItemsHandler($collectionStore, $access, $lockFactory);
    $handler(
      new ReorderCollectionItemsCommand($collectionId->toString(), [$secondItemId->toString(), $firstItemId->toString()]),
      $ownerId->toString(),
    );

    $this->assertSame(['load' => true, 'store' => true], $lockedWhile);
    $this->assertFalse($this->isLockedByAnotherRequest($lockFactory, $collectionId));
  }
}
