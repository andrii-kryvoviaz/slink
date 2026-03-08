<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Command\BatchReassignImages\Operation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Image\Application\Command\BatchReassignImages\BatchReassignImagesCommand;
use Slink\Image\Application\Command\BatchReassignImages\Operation\ReassignCollectionsOperation;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\DataStructures\HashMap;
use Slink\Shared\Domain\ValueObject\ID;

final class ReassignCollectionsOperationTest extends TestCase {
  private const USER_ID = '123e4567-e89b-12d3-a456-426614174000';
  private const IMAGE_ID = '11111111-1111-1111-1111-111111111111';
  private const COLLECTION_ID_1 = '55555555-5555-5555-5555-555555555555';
  private const COLLECTION_ID_2 = '66666666-6666-6666-6666-666666666666';
  private const OTHER_USER_ID = '99999999-9999-9999-9999-999999999999';

  /**
   * @throws Exception
   */
  #[Test]
  public function itSupportsCommandWithCollectionIds(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['collectionIds' => [self::COLLECTION_ID_1]],
    ]);

    $collectionRepository = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);

    $operation = new ReassignCollectionsOperation($collectionRepository, $collectionItemRepository);

    $this->assertTrue($operation->supports($command, self::IMAGE_ID));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itDoesNotSupportCommandWithoutCollectionIds(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['tagIds' => ['33333333-3333-3333-3333-333333333333']],
    ]);

    $collectionRepository = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);

    $operation = new ReassignCollectionsOperation($collectionRepository, $collectionItemRepository);

    $this->assertFalse($operation->supports($command, self::IMAGE_ID));
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itAddsAndRemovesCollections(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['collectionIds' => [self::COLLECTION_ID_2]],
    ]);
    $userId = ID::fromString(self::USER_ID);
    $imageId = ID::fromString(self::IMAGE_ID);

    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $collectionItemRepository->method('getCollectionIdsByImageIds')->willReturn([
      self::IMAGE_ID => [self::COLLECTION_ID_1],
    ]);

    $collectionToRemove = $this->createMock(Collection::class);
    $collectionToRemove->method('isOwnedBy')->with($userId)->willReturn(true);
    $collectionToRemove->expects($this->once())->method('removeItem')->with($imageId);

    $collectionToAdd = $this->createMock(Collection::class);
    $collectionToAdd->method('isOwnedBy')->with($userId)->willReturn(true);
    $collectionToAdd->expects($this->once())->method('addItem');

    $collectionsMap = new HashMap();
    $collectionsMap->set(self::COLLECTION_ID_1, $collectionToRemove);
    $collectionsMap->set(self::COLLECTION_ID_2, $collectionToAdd);

    $collectionRepository = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionRepository->method('getByIds')->willReturn($collectionsMap);
    $collectionRepository->expects($this->exactly(2))->method('store');

    $image = $this->createStub(Image::class);
    $image->method('aggregateRootId')->willReturn($imageId);

    $operation = new ReassignCollectionsOperation($collectionRepository, $collectionItemRepository);
    $operation->apply($image, $command, self::IMAGE_ID, $userId);
  }

  /**
   * @throws Exception
   */
  #[Test]
  public function itSkipsCollectionsNotOwnedByUser(): void {
    $command = new BatchReassignImagesCommand([
      self::IMAGE_ID => ['collectionIds' => [self::COLLECTION_ID_1, self::COLLECTION_ID_2]],
    ]);
    $userId = ID::fromString(self::USER_ID);
    $otherUserId = ID::fromString(self::OTHER_USER_ID);
    $imageId = ID::fromString(self::IMAGE_ID);

    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $collectionItemRepository->method('getCollectionIdsByImageIds')->willReturn([
      self::IMAGE_ID => [],
    ]);

    $ownedCollection = $this->createMock(Collection::class);
    $ownedCollection->method('isOwnedBy')->with($userId)->willReturn(true);
    $ownedCollection->expects($this->once())->method('addItem');

    $otherCollection = $this->createMock(Collection::class);
    $otherCollection->method('isOwnedBy')->with($userId)->willReturn(false);
    $otherCollection->expects($this->never())->method('addItem');

    $collectionsMap = new HashMap();
    $collectionsMap->set(self::COLLECTION_ID_1, $ownedCollection);
    $collectionsMap->set(self::COLLECTION_ID_2, $otherCollection);

    $collectionRepository = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionRepository->method('getByIds')->willReturn($collectionsMap);
    $collectionRepository->expects($this->once())->method('store')->with($ownedCollection);

    $image = $this->createStub(Image::class);
    $image->method('aggregateRootId')->willReturn($imageId);

    $operation = new ReassignCollectionsOperation($collectionRepository, $collectionItemRepository);
    $operation->apply($image, $command, self::IMAGE_ID, $userId);
  }
}
