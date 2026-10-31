<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Command\AddItemToCollection;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionCommand;
use Slink\Collection\Application\Command\AddItemToCollection\AddItemToCollectionHandler;
use Slink\Collection\Domain\Collection;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Enum\ItemType;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionStoreRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionDescription;
use Slink\Collection\Domain\ValueObject\CollectionName;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Image;
use Slink\Image\Domain\Repository\ImageStoreRepositoryInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Group('sc-1179')]
final class AddItemToCollectionHandlerTest extends TestCase {

  private function createLockFactory(): LockFactory {
    return new LockFactory(new InMemoryStore());
  }

  private function createImageStore(): ImageStoreRepositoryInterface&Stub {
    $imageStore = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageStore->method('get')->willReturn($this->createStub(Image::class));

    return $imageStore;
  }

  private function createMissingImageStore(): ImageStoreRepositoryInterface&Stub {
    $imageStore = $this->createStub(ImageStoreRepositoryInterface::class);
    $imageStore->method('get')->willThrowException(new NotFoundException());

    return $imageStore;
  }

  private function createAccess(bool $canManageItems, bool $canEditImage): AuthorizationCheckerInterface&Stub {
    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access
      ->method('isGranted')
      ->willReturnCallback(fn (mixed $attribute): bool => match ($attribute) {
        CollectionAccess::ManageItems => $canManageItems,
        ImageAccess::Edit => $canEditImage,
        default => false,
      });

    return $access;
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
  public function itAddsItemToCollection(): void {
    $userId = ID::generate();
    $collectionId = ID::generate();
    $itemId = ID::generate();

    $collection = $this->createCollection($collectionId, $userId);

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

    $handler = new AddItemToCollectionHandler(
      $collectionStore,
      $this->createImageStore(),
      $this->createAccess(true, true),
      $this->createLockFactory(),
    );

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

    $collectionStore = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturn($this->createCollection($collectionId, $ownerId));

    $handler = new AddItemToCollectionHandler(
      $collectionStore,
      $this->createImageStore(),
      $this->createAccess(false, true),
      $this->createLockFactory(),
    );

    $command = new AddItemToCollectionCommand(
      $collectionId->toString(),
      $itemId->toString(),
      ItemType::Image
    );

    $handler($command, $differentUserId->toString());
  }

  #[Test]
  public function itThrowsForbiddenWhenUserCannotEditImage(): void {
    $this->expectException(ForbiddenException::class);

    $ownerId = ID::generate();
    $collectionId = ID::generate();
    $itemId = ID::generate();

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturn($this->createCollection($collectionId, $ownerId));

    $collectionStore
      ->expects($this->never())
      ->method('store');

    $handler = new AddItemToCollectionHandler(
      $collectionStore,
      $this->createImageStore(),
      $this->createAccess(true, false),
      $this->createLockFactory(),
    );

    $command = new AddItemToCollectionCommand(
      $collectionId->toString(),
      $itemId->toString(),
      ItemType::Image
    );

    $handler($command, $ownerId->toString());
  }

  #[Test]
  public function itThrowsNotFoundWhenImageIsMissing(): void {
    $this->expectException(NotFoundException::class);

    $ownerId = ID::generate();
    $collectionId = ID::generate();
    $itemId = ID::generate();

    $collectionStore = $this->createMock(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturn($this->createCollection($collectionId, $ownerId));

    $collectionStore
      ->expects($this->never())
      ->method('store');

    $handler = new AddItemToCollectionHandler(
      $collectionStore,
      $this->createMissingImageStore(),
      $this->createAccess(true, true),
      $this->createLockFactory(),
    );

    $command = new AddItemToCollectionCommand(
      $collectionId->toString(),
      $itemId->toString(),
      ItemType::Image
    );

    $handler($command, $ownerId->toString());
  }

  #[Test]
  public function itHoldsTheCollectionItemsLockFromLoadThroughStore(): void {
    $userId = ID::generate();
    $collectionId = ID::generate();
    $lockFactory = $this->createLockFactory();
    $collection = $this->createCollection($collectionId, $userId);

    $isLockedByAnotherRequest = function () use ($lockFactory, $collectionId): bool {
      $competing = $lockFactory->createLock('collection-items-' . $collectionId->toString());

      if (!$competing->acquire()) {
        return true;
      }

      $competing->release();

      return false;
    };

    $lockedWhile = [];

    $collectionStore = $this->createStub(CollectionStoreRepositoryInterface::class);
    $collectionStore
      ->method('get')
      ->willReturnCallback(function () use (&$lockedWhile, $isLockedByAnotherRequest, $collection): Collection {
        $lockedWhile['load'] = $isLockedByAnotherRequest();

        return $collection;
      });
    $collectionStore
      ->method('store')
      ->willReturnCallback(function () use (&$lockedWhile, $isLockedByAnotherRequest): void {
        $lockedWhile['store'] = $isLockedByAnotherRequest();
      });

    $handler = new AddItemToCollectionHandler(
      $collectionStore,
      $this->createImageStore(),
      $this->createAccess(true, true),
      $lockFactory,
    );

    $handler(
      new AddItemToCollectionCommand($collectionId->toString(), ID::generate()->toString(), ItemType::Image),
      $userId->toString(),
    );

    $this->assertSame(['load' => true, 'store' => true], $lockedWhile);
    $this->assertFalse($isLockedByAnotherRequest());
  }
}
