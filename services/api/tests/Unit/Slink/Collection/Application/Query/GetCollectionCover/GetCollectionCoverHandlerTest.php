<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Query\GetCollectionCover;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Query\GetCollectionCover\GetCollectionCoverHandler;
use Slink\Collection\Application\Query\GetCollectionCover\GetCollectionCoverQuery;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetCollectionCoverHandlerTest extends TestCase {
  private CollectionRepositoryInterface $collectionRepository;
  private CollectionItemRepositoryInterface $collectionItemRepository;
  private CollectionCoverGeneratorInterface $coverGenerator;
  private AuthorizationCheckerInterface $access;
  private GetCollectionCoverHandler $handler;

  protected function setUp(): void {
    $this->collectionRepository = $this->createStub(CollectionRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $this->coverGenerator = $this->createStub(CollectionCoverGeneratorInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
    $this->access->method('isGranted')->willReturn(true);

    $this->handler = new GetCollectionCoverHandler(
      $this->collectionRepository,
      $this->collectionItemRepository,
      $this->coverGenerator,
      $this->access,
    );
  }

  #[Test]
  public function itThrowsNotFoundExceptionWhenCollectionDoesNotExist(): void {
    $collectionId = 'collection-id';
    $userId = 'user-id';

    $this->collectionRepository
      ->method('findById')
      ->willReturnMap([
        [$collectionId, null],
      ]);

    $query = new GetCollectionCoverQuery($collectionId);

    $this->expectException(NotFoundException::class);

    ($this->handler)($query, $userId);
  }

  #[Test]
  public function itThrowsForbiddenExceptionWhenUserIsNotOwner(): void {
    $collectionId = 'collection-id';
    $ownerId = 'owner-id';
    $userId = 'other-user-id';

    $collection = $this->createStub(CollectionView::class);
    $collection->method('getUserId')->willReturn($ownerId);

    $this->collectionRepository
      ->method('findById')
      ->willReturnMap([
        [$collectionId, $collection],
      ]);

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(false);

    $handler = new GetCollectionCoverHandler(
      $this->collectionRepository,
      $this->collectionItemRepository,
      $this->coverGenerator,
      $access,
    );

    $query = new GetCollectionCoverQuery($collectionId);

    $this->expectException(ForbiddenException::class);

    ($handler)($query, $userId);
  }

  #[Test]
  public function itThrowsNotFoundExceptionWhenCollectionHasNoImages(): void {
    $collectionId = 'collection-id';
    $userId = 'user-id';

    $collection = $this->createStub(CollectionView::class);
    $collection->method('getUserId')->willReturn($userId);

    $this->collectionRepository
      ->method('findById')
      ->willReturnMap([
        [$collectionId, $collection],
      ]);

    $this->collectionItemRepository
      ->method('getFirstImageIdsByCollectionIds')
      ->willReturnMap([
        [[$collectionId], 5, []],
      ]);

    $query = new GetCollectionCoverQuery($collectionId);

    $this->expectException(NotFoundException::class);

    ($this->handler)($query, $userId);
  }

  #[Test]
  public function itReturnsCoverContentWhenSuccessful(): void {
    $collectionId = 'collection-id';
    $userId = 'user-id';
    $imageIds = ['image-1', 'image-2'];
    $coverContent = 'binary-image-content';

    $collection = $this->createStub(CollectionView::class);
    $collection->method('getUserId')->willReturn($userId);

    $collectionRepository = $this->createStub(CollectionRepositoryInterface::class);
    $collectionRepository
      ->method('findById')
      ->willReturnMap([
        [$collectionId, $collection],
      ]);

    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $collectionItemRepository
      ->method('getFirstImageIdsByCollectionIds')
      ->willReturnMap([
        [[$collectionId], 5, [$collectionId => $imageIds]],
      ]);

    $coverGenerator = $this->createMock(CollectionCoverGeneratorInterface::class);
    $coverGenerator
      ->expects($this->once())
      ->method('getCoverContent')
      ->with($collectionId, $imageIds)
      ->willReturn($coverContent);

    $access = $this->createStub(AuthorizationCheckerInterface::class);
    $access->method('isGranted')->willReturn(true);

    $handler = new GetCollectionCoverHandler(
      $collectionRepository,
      $collectionItemRepository,
      $coverGenerator,
      $access,
    );
    $query = new GetCollectionCoverQuery($collectionId);

    $result = ($handler)($query, $userId);

    $this->assertEquals($coverContent, $result);
  }
}
