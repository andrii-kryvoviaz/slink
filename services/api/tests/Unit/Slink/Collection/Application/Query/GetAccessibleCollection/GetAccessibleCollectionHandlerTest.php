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
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
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

  private function createShareResponse(string $shareId = 'share-id', string $shareUrl = 'https://example.com/c/abc'): ShareResponse {
    $shareable = $this->createStub(ShareableReference::class);
    $shareable->method('getShareableType')->willReturn(ShareableType::Collection);

    $share = $this->createStub(ShareView::class);
    $share->method('getId')->willReturn($shareId);
    $share->method('getShareable')->willReturn($shareable);
    $share->method('getExpiresAt')->willReturn(null);

    return ShareResponse::fromShare($share, $shareUrl);
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
    $this->ownerShareInfoResolver->method('resolve')->willReturn(null);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $requesterId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('userId', $result->resource);
    $this->assertEquals($ownerId, $result->resource['userId']);
    $this->assertArrayNotHasKey('sharing', $result->resource);
  }

  #[Test]
  public function itReturnsItemWithSharingWhenOwnerAndShareExists(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);

    $sharing = $this->createShareResponse('share-id', 'https://example.com/c/abc');
    $this->ownerShareInfoResolver->method('resolve')->willReturn($sharing);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('sharing', $result->resource);
    $this->assertInstanceOf(ShareResponse::class, $result->resource['sharing']);
    $this->assertSame('share-id', $result->resource['sharing']->getShareId());
    $this->assertSame('https://example.com/c/abc', $result->resource['sharing']->getShareUrl());
    $this->assertSame(ShareableType::Collection, $result->resource['sharing']->getType());
  }

  #[Test]
  public function itReturnsItemWithoutSharingWhenOwnerAndNoShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);
    $this->ownerShareInfoResolver->method('resolve')->willReturn(null);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayNotHasKey('sharing', $result->resource);
  }

  #[Test]
  public function itReturnsItemWhenUnpublishedShareAndOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $collection = $this->createCollection($ownerId);
    $this->collectionRepository->method('findById')->willReturn($collection);
    $this->access->method('isGranted')->willReturn(true);

    $sharing = $this->createShareResponse();
    $this->ownerShareInfoResolver->method('resolve')->willReturn($sharing);

    $handler = $this->createHandler();
    $result = $handler(new GetAccessibleCollectionQuery('collection-id'), $ownerId);

    $this->assertInstanceOf(Item::class, $result);
    $this->assertIsArray($result->resource);
    $this->assertArrayHasKey('sharing', $result->resource);
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
