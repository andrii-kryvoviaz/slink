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

final class ReorderCollectionItemsHandlerTest extends TestCase {

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

    $handler = new ReorderCollectionItemsHandler($collectionStore);

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

    $handler = new ReorderCollectionItemsHandler($collectionStore);

    $command = new ReorderCollectionItemsCommand($collectionId->toString(), []);

    $handler($command, $differentUserId->toString());
  }
}
