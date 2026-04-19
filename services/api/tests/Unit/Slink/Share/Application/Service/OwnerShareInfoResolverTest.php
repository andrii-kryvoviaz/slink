<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Share\Application\Service\OwnerShareInfoResolver;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;

final class OwnerShareInfoResolverTest extends TestCase {
  private ShareRepositoryInterface&Stub $shareRepository;
  private ShareServiceInterface&Stub $shareService;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->shareService = $this->createStub(ShareServiceInterface::class);
  }

  private function createResolver(): OwnerShareInfoResolver {
    return new OwnerShareInfoResolver(
      $this->shareRepository,
      $this->shareService,
    );
  }

  #[Test]
  public function itReturnsNullWhenViewerIsNull(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, null);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsNullWhenOwnerIsNull(): void {
    $viewerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, null, $viewerId);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsNullWhenViewerIsNotOwner(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');
    $viewerId = ID::fromString('660e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $viewerId);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsNullWhenOwnerHasNoShare(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $this->shareRepository
      ->method('findByShareable')
      ->with('shareable-id', ShareableType::Collection)
      ->willReturn(null);

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $ownerId);

    $this->assertNull($result);
  }

  #[Test]
  public function itReturnsShareResponseWhenOwnerHasShare(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $shareable = $this->createStub(ShareableReference::class);
    $shareable->method('getShareableType')->willReturn(ShareableType::Collection);

    $share = $this->createStub(ShareView::class);
    $share->method('getId')->willReturn('share-id');
    $share->method('getShareable')->willReturn($shareable);
    $share->method('getExpiresAt')->willReturn(null);

    $this->shareRepository
      ->method('findByShareable')
      ->with('shareable-id', ShareableType::Collection)
      ->willReturn($share);

    $this->shareService->method('resolveUrl')->willReturn('https://example.com/c/abc');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $ownerId);

    $this->assertInstanceOf(ShareResponse::class, $result);
    $this->assertSame('share-id', $result->getShareId());
    $this->assertSame('https://example.com/c/abc', $result->getShareUrl());
    $this->assertSame(ShareableType::Collection, $result->getType());
    $this->assertFalse($result->wasCreated());
    $this->assertNull($result->getExpiresAt());
  }
}
