<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Command\CreateCollection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Command\CreateCollection\CreateCollectionCommand;
use Slink\Collection\Application\Command\CreateCollection\CreateCollectionHandler;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final class CreateCollectionHandlerTest extends TestCase {
  private CollectionStoreRepositoryInterface&MockObject $collectionStore;
  private CreateCollectionHandler $handler;

  protected function setUp(): void {
    $this->collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $this->handler = new CreateCollectionHandler($this->collectionStore);
  }

  #[Test]
  public function itCreatesCollection(): void {
    $userId = ID::generate()->toString();
    $command = new CreateCollectionCommand('My Collection', 'Test description');

    $this->collectionStore
      ->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Collection $collection) use ($userId) {
        return $collection->getName()->toString() === 'My Collection'
          && $collection->getDescription()->toString() === 'Test description'
          && $collection->getUserId()->toString() === $userId;
      }));

    ($this->handler)($command, $userId);
  }

  #[Test]
  public function itCreatesCollectionWithEmptyDescription(): void {
    $userId = ID::generate()->toString();
    $command = new CreateCollectionCommand('My Collection', '');

    $this->collectionStore
      ->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Collection $collection) {
        return $collection->getDescription()->toString() === '';
      }));

    ($this->handler)($command, $userId);
  }
}
