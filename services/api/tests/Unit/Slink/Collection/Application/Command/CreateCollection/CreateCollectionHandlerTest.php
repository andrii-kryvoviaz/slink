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
use Slink\Collection\Domain\Service\UniqueCollectionNameGeneratorInterface;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Shared\Domain\ValueObject\ID;

final class CreateCollectionHandlerTest extends TestCase {
  private CollectionStoreRepositoryInterface&MockObject $collectionStore;
  private UniqueCollectionNameGeneratorInterface&MockObject $nameGenerator;
  private CreateCollectionHandler $handler;

  protected function setUp(): void {
    $this->collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $this->nameGenerator = $this->createMock(UniqueCollectionNameGeneratorInterface::class);
    $this->handler = new CreateCollectionHandler($this->collectionStore, $this->nameGenerator);
  }

  #[Test]
  public function itCreatesCollection(): void {
    $userId = ID::generate()->toString();
    $command = new CreateCollectionCommand('My Collection', 'Test description');

    $this->nameGenerator
      ->expects($this->once())
      ->method('generate')
      ->willReturnCallback(fn(CollectionName $name) => $name);

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

    $this->nameGenerator
      ->expects($this->once())
      ->method('generate')
      ->willReturnCallback(fn(CollectionName $name) => $name);

    $this->collectionStore
      ->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Collection $collection) {
        return $collection->getDescription()->toString() === '';
      }));

    ($this->handler)($command, $userId);
  }

  #[Test]
  public function itUsesGeneratedUniqueName(): void {
    $userId = ID::generate()->toString();
    $command = new CreateCollectionCommand('My Collection', '');

    $this->nameGenerator
      ->expects($this->once())
      ->method('generate')
      ->willReturn(CollectionName::fromString('My Collection (1)'));

    $this->collectionStore
      ->expects($this->once())
      ->method('store')
      ->with($this->callback(function (Collection $collection) {
        return $collection->getName()->toString() === 'My Collection (1)';
      }));

    ($this->handler)($command, $userId);
  }
}
