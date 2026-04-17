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
  public function itReturnsEmptyArrayWhenViewerIsNull(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, null);

    $this->assertSame([], $result);
  }

  #[Test]
  public function itReturnsEmptyArrayWhenOwnerIsNull(): void {
    $viewerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, null, $viewerId);

    $this->assertSame([], $result);
  }

  #[Test]
  public function itReturnsEmptyArrayWhenViewerIsNotOwner(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');
    $viewerId = ID::fromString('660e8400-e29b-41d4-a716-446655440000');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $viewerId);

    $this->assertSame([], $result);
  }

  #[Test]
  public function itReturnsEmptyArrayWhenOwnerHasNoShare(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $this->shareRepository
      ->method('findByShareable')
      ->with('shareable-id', ShareableType::Collection)
      ->willReturn(null);

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $ownerId);

    $this->assertSame([], $result);
  }

  #[Test]
  public function itReturnsShareInfoWhenOwnerHasShare(): void {
    $ownerId = ID::fromString('550e8400-e29b-41d4-a716-446655440000');

    $share = $this->createStub(ShareView::class);
    $share->method('getId')->willReturn('share-id');

    $this->shareRepository
      ->method('findByShareable')
      ->with('shareable-id', ShareableType::Collection)
      ->willReturn($share);

    $this->shareService->method('resolveUrl')->willReturn('https://example.com/c/abc');

    $resolver = $this->createResolver();
    $result = $resolver->resolve('shareable-id', ShareableType::Collection, $ownerId, $ownerId);

    $this->assertSame([
      'shareInfo' => [
        'shareId' => 'share-id',
        'shareUrl' => 'https://example.com/c/abc',
        'type' => ShareableType::Collection->value,
        'expiresAt' => null,
      ],
    ], $result);
  }
}
