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
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class CollectionScopedImageVoterTest extends TestCase {
  private const string OWNER_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const string ANONYMOUS_ID = '';

  private ShareRepositoryInterface&Stub $shareRepository;
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;
  private ShareAccessGuard $accessGuard;
  private ConfigurationProviderInterface&Stub $configurationProvider;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $this->accessGuard = $this->createStub(ShareAccessGuard::class);
    $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $this->configurationProvider->method('get')->willReturn(false);
  }

  private function createVoter(): CollectionScopedImageVoter {
    return new CollectionScopedImageVoter(
      $this->shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
      $this->configurationProvider,
    );
  }

  private function createToken(string $userIdentifier = self::ANONYMOUS_ID): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    if ($userIdentifier !== self::ANONYMOUS_ID) {
      $token->method('getUser')->willReturn($this->createStub(UserInterface::class));
    } else {
      $token->method('getUser')->willReturn(null);
    }

    return $token;
  }

  private function createImageView(?string $ownerId): ImageView&Stub {
    $image = $this->createStub(ImageView::class);
    $image
      ->method('isOwnedBy')
      ->willReturnCallback(fn (?ID $userId): bool => $ownerId !== null && $userId?->equals(ID::fromString($ownerId)) === true);

    return $image;
  }

  private function createContext(
    string $collectionId = 'collection-id',
    string $itemId = 'item-id',
    ?string $ownerId = null,
  ): CollectionScopedImageAccessContext {
    return new CollectionScopedImageAccessContext($collectionId, $itemId, $this->createImageView($ownerId));
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
      $this->configurationProvider,
    );

    $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);
  }

  #[Test]
  public function itGrantsAccessToOwnerEvenWhenShareIsPasswordProtected(): void {
    $accessGuard = $this->createMock(ShareAccessGuard::class);
    $accessGuard->expects($this->never())->method('allows');

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository->expects($this->never())->method('findByShareable');

    $voter = new CollectionScopedImageVoter(
      $shareRepository,
      $this->collectionItemRepository,
      $accessGuard,
      $this->configurationProvider,
    );

    $context = $this->createContext(ownerId: self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $context, [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsAccessToOwnerEvenWhenShareIsMissing(): void {
    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();
    $context = $this->createContext(ownerId: self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $context, [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
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
      $this->configurationProvider,
    );

    $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);
  }

  #[Test]
  public function itDeniesAnonymousAccessWhenRequireAuthForSharesEnabled(): void {
    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository->expects($this->never())->method('findByShareable');

    $accessGuard = $this->createMock(ShareAccessGuard::class);
    $accessGuard->expects($this->never())->method('allows');

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->willReturn(true);

    $voter = new CollectionScopedImageVoter(
      $shareRepository,
      $this->collectionItemRepository,
      $accessGuard,
      $configurationProvider,
    );

    $result = $voter->vote($this->createToken(), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsAccessToOwnerEvenWhenRequireAuthForSharesEnabled(): void {
    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->willReturn(true);

    $voter = new CollectionScopedImageVoter(
      $this->shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
      $configurationProvider,
    );

    $context = $this->createContext(ownerId: self::OWNER_ID);

    $result = $voter->vote($this->createToken(self::OWNER_ID), $context, [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsAccessToLoggedInNonOwnerWithAccessibleShareWhenRequireAuthForSharesEnabled(): void {
    $shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));

    $accessGuard = $this->createStub(ShareAccessGuard::class);
    $accessGuard->method('allows')->willReturn(true);

    $collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->willReturn(true);

    $voter = new CollectionScopedImageVoter(
      $shareRepository,
      $collectionItemRepository,
      $accessGuard,
      $configurationProvider,
    );

    $result = $voter->vote($this->createToken('660e8400-e29b-41d4-a716-446655440000'), $this->createContext(), [CollectionScopedImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }
}
