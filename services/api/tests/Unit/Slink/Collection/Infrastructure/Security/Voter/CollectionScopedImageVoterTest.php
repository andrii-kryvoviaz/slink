<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\ValueObject\CollectionScopedImageAccessContext;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Collection\Infrastructure\Security\Voter\CollectionScopedImageVoter;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class CollectionScopedImageVoterTest extends TestCase {
  private ShareRepositoryInterface&Stub $shareRepository;
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;
  private ShareAccessGuard $accessGuard;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $this->accessGuard = $this->createStub(ShareAccessGuard::class);
  }

  private function createVoter(): CollectionScopedImageVoter {
    return new CollectionScopedImageVoter(
      $this->shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
    );
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function createContext(string $collectionId = 'collection-id', string $itemId = 'item-id'): CollectionScopedImageAccessContext {
    return new CollectionScopedImageAccessContext($collectionId, $itemId);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), $this->createContext(), ['unknown.attribute']);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itDeniesWhenShareNotFound(): void {
    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenShareAccessGuardDenies(): void {
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenItemNotInCollection(): void {
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->accessGuard->method('allows')->willReturn(true);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn(null);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsWhenShareAccessibleAndItemBelongsToCollection(): void {
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->accessGuard->method('allows')->willReturn(true);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itLooksUpShareByCollectionId(): void {
    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('findByShareable')
      ->with('collection-id', ShareableType::Collection)
      ->willReturn($this->createStub(ShareView::class));

    $this->accessGuard->method('allows')->willReturn(true);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));

    $voter = new CollectionScopedImageVoter(
      $shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
    );

    $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);
  }

  #[Test]
  public function itLooksUpItemByCollectionAndItemId(): void {
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->accessGuard->method('allows')->willReturn(true);

    $collectionItemRepository = $this->createMock(CollectionItemRepositoryInterface::class);
    $collectionItemRepository
      ->expects($this->once())
      ->method('findByCollectionAndItemId')
      ->with('collection-id', 'item-id')
      ->willReturn($this->createStub(CollectionItemView::class));

    $voter = new CollectionScopedImageVoter(
      $this->shareRepository,
      $collectionItemRepository,
      $this->accessGuard,
    );

    $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);
  }
}
