<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchImages\Operation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\BatchImages\BatchImagesCommand;
use Slink\Image\Application\Command\BatchImages\Operation\AddToCollectionOperation;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AddToCollectionOperationTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const COLLECTION_ID = '44444444-4444-4444-4444-444444444444';
  private const IMAGE_ID = '11111111-1111-1111-1111-111111111111';

  /**
   * @throws Exception
   */
  #[Test]
  public function itSupportsCommandWithCollectionId(): void {
    $command = new BatchImagesCommand([], collectionId: self::COLLECTION_ID);
    $collectionRepository = $this->createStub(CollectionStoreRepositoryInterface::class);

    $operation = new AddToCollectionOperation($collectionRepository);

    $this->assertTrue($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDoesNotSupportCommandWithoutCollectionId(): void {
    $command = new BatchImagesCommand([]);
    $collectionRepository = $this->createStub(CollectionStoreRepositoryInterface::class);

    $operation = new AddToCollectionOperation($collectionRepository);

    $this->assertFalse($operation->supports($command));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itAddsImageToCollection(): void {
    $command = new BatchImagesCommand([], collectionId: self::COLLECTION_ID);
    $userId = ID::fromString(self::USER_ID);
    $imageId = ID::fromString(self::IMAGE_ID);

    $collection = $this->createMock(Collection::class);
    $collection->method('isOwnedBy')->with($userId)->willReturn(true);
    $collection->expects($this->once())->method('addItem');

    $collectionRepository = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionRepository->method('get')->willReturn($collection);
    $collectionRepository->expects($this->once())->method('store')->with($collection);

    $image = $this->createStub(Image::class);
    $image->method('aggregateRootId')->willReturn($imageId);

    $operation = new AddToCollectionOperation($collectionRepository);
    $operation->apply($image, $command, $userId);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itThrowsAccessDeniedIfCollectionNotOwnedByUser(): void {
    $command = new BatchImagesCommand([], collectionId: self::COLLECTION_ID);
    $userId = ID::fromString(self::USER_ID);

    $collection = $this->createStub(Collection::class);
    $collection->method('isOwnedBy')->with($userId)->willReturn(false);

    $collectionRepository = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionRepository->method('get')->willReturn($collection);

    $image = $this->createStub(Image::class);

    $operation = new AddToCollectionOperation($collectionRepository);

    $this->expectException(AccessDeniedException::class);
    $operation->apply($image, $command, $userId);
  }
}
