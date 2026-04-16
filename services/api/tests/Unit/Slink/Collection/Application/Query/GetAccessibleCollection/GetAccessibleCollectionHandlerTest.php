<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Application\Query\GetAccessibleCollection;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Application\Query\GetAccessibleCollection\GetAccessibleCollectionHandler;
use Slink\Collection\Application\Query\GetAccessibleCollection\GetAccessibleCollectionQuery;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\OwnerShareInfoResolverInterface;
use Slink\Shared\Application\Http\Item;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class GetAccessibleCollectionHandlerTest extends TestCase {
  private CollectionRepositoryInterface&Stub $collectionRepository;
  private OwnerShareInfoResolverInterface&Stub $ownerShareInfoResolver;
  private AuthorizationCheckerInterface&Stub $access;

  protected function setUp(): void {
    parent::setUp();

    $this->collectionRepository = $this->createStub(CollectionRepositoryInterface::class);
    $this->ownerShareInfoResolver = $this->createStub(OwnerShareInfoResolverInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
  }

  private function createHandler(): GetAccessibleCollectionHandler {
    return new GetAccessibleCollectionHandler(
      $this->collectionRepository,
      $this->ownerShareInfoResolver,
      $this->access,
    );
  }

  private function createCollection(string $ownerId, string $collectionId = 'collection-id'): CollectionView {
    $collection = $this->createStub(CollectionView::class);
    $collection->method('getId')->willReturn($collectionId);
    $collection->method('getUuid')->willReturn($collectionId);
    $collection->method('getUserId')->willReturn($ownerId);

    return $collection;
  }

  #[Test]
  public function itReturnsNullWhenCollectionNotFound(): void {
    $this->collectionRepository->method('findById')->willReturn(null);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('missing-id'), 'user-id');

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsNullWhenUnpublishedShareAndNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(false);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $requesterId);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsItemWhenPublishedShareAndNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')
      ->with(CollectionAccess::View, $collection)
      ->willReturn(true);
    $this->ownerShareInfoResolver->method('resolve')->willReturn([]);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $requesterId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('userId', $result->resource);
    $this->assertEquals($ownerId, $result->resource['userId']);
    $this->assertArrayNotHasKey('shareInfo', $result->resource);
  }

  #[Test]
  public function itReturnsItemWithShareInfoWhenOwnerAndShareExists(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);
    $this->ownerShareInfoResolver->method('resolve')->willReturn([
      'shareInfo' => [
        'shareId' => 'share-id',
        'shareUrl' => 'https://example.com/c/abc',
        'type' => ShareableType::Collection->value,
      ],
    ]);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('shareInfo', $result->resource);
    $this->assertEquals('share-id', $result->resource['shareInfo']['shareId']);
    $this->assertEquals('https://example.com/c/abc', $result->resource['shareInfo']['shareUrl']);
    $this->assertEquals(ShareableType::Collection->value, $result->resource['shareInfo']['type']);
  }

  #[Test]
  public function itReturnsItemWithoutShareInfoWhenOwnerAndNoShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);
    $this->ownerShareInfoResolver->method('resolve')->willReturn([]);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayNotHasKey('shareInfo', $result->resource);
  }

  #[Test]
  public function itReturnsItemWhenUnpublishedShareAndOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);
    $this->ownerShareInfoResolver->method('resolve')->willReturn([
      'shareInfo' => [
        'shareId' => 'share-id',
        'shareUrl' => 'https://example.com/c/abc',
        'type' => ShareableType::Collection->value,
      ],
    ]);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('shareInfo', $result->resource);
  }

  #[Test]
  public function itReturnsNullWhenNoShareAndNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(false);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $requesterId);

    $this->assertNull($result);
  }
}
