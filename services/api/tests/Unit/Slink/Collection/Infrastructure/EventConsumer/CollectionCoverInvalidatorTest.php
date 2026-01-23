<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\EventConsumer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Event\CollectionItemsWereReordered;
use Slink\Collection\Domain\Event\CollectionWasDeleted;
use Slink\Collection\Domain\Event\ItemWasAddedToCollection;
use Slink\Collection\Domain\Event\ItemWasRemovedFromCollection;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Collection\Domain\ValueObject\CollectionItem;
use Slink\Collection\Infrastructure\EventConsumer\CollectionCoverInvalidator;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;

final class CollectionCoverInvalidatorTest extends TestCase {
  private MockObject&CollectionCoverGeneratorInterface $coverGenerator;
  private CollectionCoverInvalidator $invalidator;

  protected function setUp(): void {
    $this->coverGenerator = $this->createMock(CollectionCoverGeneratorInterface::class);
    $this->invalidator = new CollectionCoverInvalidator($this->coverGenerator);
  }

  #[Test]
  public function itInvalidatesCoverWhenItemIsAdded(): void {
    $collectionId = ID::generate();
    $item = CollectionItem::create(ID::generate(), ItemType::Image, 0);

    $event = new ItemWasAddedToCollection(
      $collectionId,
      $item,
    );

    $this->coverGenerator
      ->expects($this->once())
      ->method('invalidateCover')
      ->with($collectionId->toString());

    $this->invalidator->handleItemWasAddedToCollection($event);
  }

  #[Test]
  public function itInvalidatesCoverWhenItemIsRemoved(): void {
    $collectionId = ID::generate();

    $event = new ItemWasRemovedFromCollection(
      $collectionId,
      ID::generate(),
    );

    $this->coverGenerator
      ->expects($this->once())
      ->method('invalidateCover')
      ->with($collectionId->toString());

    $this->invalidator->handleItemWasRemovedFromCollection($event);
  }

  #[Test]
  public function itInvalidatesCoverWhenItemsAreReordered(): void {
    $collectionId = ID::generate();

    $event = new CollectionItemsWereReordered(
      $collectionId,
      ['item-1', 'item-2'],
    );

    $this->coverGenerator
      ->expects($this->once())
      ->method('invalidateCover')
      ->with($collectionId->toString());

    $this->invalidator->handleCollectionItemsWereReordered($event);
  }

  #[Test]
  public function itInvalidatesCoverWhenCollectionIsDeleted(): void {
    $collectionId = ID::generate();

    $event = new CollectionWasDeleted($collectionId, DateTime::now());

    $this->coverGenerator
      ->expects($this->once())
      ->method('invalidateCover')
      ->with($collectionId->toString());

    $this->invalidator->handleCollectionWasDeleted($event);
  }
}
