<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Collection\Infrastructure\Security\Voter\CollectionVoter;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class CollectionVoterTest extends TestCase {
  private ShareRepositoryInterface&Stub $shareRepository;
  private ShareAccessGuard $accessGuard;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->accessGuard = $this->createStub(ShareAccessGuard::class);
  }

  private function createVoter(): CollectionVoter {
    return new CollectionVoter($this->shareRepository, $this->accessGuard);
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function createCollectionView(string $ownerId, string $collectionId = '550e8400-e29b-41d4-a716-446655440000'): CollectionView&Stub {
    $collection = $this->createStub(CollectionView::class);
    $collection->method('getId')->willReturn($collectionId);
    $collection->method('getUuid')->willReturn($collectionId);
    $collection->method('getUserId')->willReturn($ownerId);

    return $collection;
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();
    $collection = $this->createCollectionView('550e8400-e29b-41d4-a716-446655440000');

    $result = $voter->vote($this->createToken(), $collection, ['unknown.attribute']);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsViewToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsViewToNonOwnerWhenShareIsAccessible(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->accessGuard->method('allows')->willReturn(true);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenShareIsUnpublished(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenShareIsLocked(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenShareIsExpired(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewToNonOwnerWhenNoShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewForAnonymousToken(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken(''), $collection, [CollectionAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsEditToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $collection, [CollectionAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesEditToNonOwnerEvenWithAccessibleShare(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->accessGuard->method('allows')->willReturn(true);

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsDeleteToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $collection, [CollectionAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesDeleteToNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::Delete]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsManageItemsToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $collection, [CollectionAccess::ManageItems]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesManageItemsToNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $collection = $this->createCollectionView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::ManageItems]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itLooksUpShareableByCollectionType(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';
    $collectionId = '770e8400-e29b-41d4-a716-446655440000';

    $share = $this->createStub(ShareView::class);

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('findByShareable')
      ->with($collectionId, ShareableType::Collection)
      ->willReturn($share);

    $accessGuard = $this->createStub(ShareAccessGuard::class);
    $accessGuard->method('allows')->willReturn(true);

    $voter = new CollectionVoter($shareRepository, $accessGuard);
    $collection = $this->createCollectionView($ownerId, $collectionId);

    $voter->vote($this->createToken($requesterId), $collection, [CollectionAccess::View]);
  }
}
