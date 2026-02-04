<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Command\AddItemToCollection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionCommand;
use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionHandler;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;

final class AddItemToCollectionHandlerTest extends TestCase {

  #[Test]
  public function itAddsItemToCollection(): void {
    $userId = ID::generate();
    $collectionId = ID::generate();
    $itemId = ID::generate();

    $collection = Collection::create(
      $collectionId,
      $userId,
      CollectionName::fromString('Test'),
      CollectionDescription::fromString('Test')
    );
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

    $handler = new AddItemToCollectionHandler($collectionStore);

    $command = new AddItemToCollectionCommand(
      $collectionId->toString(),
      $itemId->toString(),
      ItemType::Image
    );

    $handler($command, $userId->toString());
  }

  #[Test]
  public function itThrowsExceptionWhenUserDoesNotOwnCollection(): void {
    $this->expectException(CollectionAccessDeniedException::class);

    $ownerId = ID::generate();
    $differentUserId = ID::generate();
    $collectionId = ID::generate();
    $itemId = ID::generate();

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

    $handler = new AddItemToCollectionHandler($collectionStore);

    $command = new AddItemToCollectionCommand(
      $collectionId->toString(),
      $itemId->toString(),
      ItemType::Image
    );

    $handler($command, $differentUserId->toString());
  }
}
