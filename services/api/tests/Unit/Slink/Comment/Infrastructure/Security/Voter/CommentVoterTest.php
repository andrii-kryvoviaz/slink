<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Comment\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Enum\CollectionScopedImageAccess;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Collection\Infrastructure\Security\Voter\CollectionScopedImageVoter;
use Slink\Comment\Domain\Enum\CommentAccess;
use Slink\Comment\Infrastructure\Security\Voter\CommentVoter;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\ValueObject\ImageAttributes;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Security\Voter\GuestAccessVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoterTest extends TestCase {
  private const string IMAGE_ID = 'image-123';
  private const string OWNER_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const string NON_OWNER_ID = '660e8400-e29b-41d4-a716-446655440000';
  private const string SHARE_VIEWER_ID = '770e8400-e29b-41d4-a716-446655440000';

  private ImageRepositoryInterface&Stub $imageRepository;
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;
  private AuthorizationCheckerInterface&Stub $access;

  protected function setUp(): void {
    parent::setUp();

    $this->imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $this->access = $this->createStub(AuthorizationCheckerInterface::class);
  }

  private function createVoter(): CommentVoter {
    return new CommentVoter(
      $this->imageRepository,
      $this->collectionItemRepository,
      $this->access,
    );
  }

  private function stubAccess(bool $guestAllowed = false): void {
    $this->access->method('isGranted')->willReturnCallback(
      fn (mixed $attribute, mixed $subject = null): bool =>
        $attribute === GuestAccessVoter::GUEST_VIEW_ALLOWED && $guestAllowed
    );
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    if ($userIdentifier !== '') {
      $token->method('getUser')->willReturn($this->createStub(UserInterface::class));
    } else {
      $token->method('getUser')->willReturn(null);
    }

    return $token;
  }

  private function createImageView(?string $ownerId, bool $isPublic = false): ImageView&Stub {
    $attributes = $this->createStub(ImageAttributes::class);
    $attributes->method('isPublic')->willReturn($isPublic);

    $image = $this->createStub(ImageView::class);
    $image->method('getAttributes')->willReturn($attributes);
    $image
      ->method('isOwnedBy')
      ->willReturnCallback(fn (?ID $userId): bool => $ownerId !== null && $userId?->equals(ID::fromString($ownerId)) === true);

    return $image;
  }

  private function stubImage(?string $ownerId, bool $isPublic = false): void {
    $this->imageRepository->method('oneById')->willReturn($this->createImageView($ownerId, $isPublic));
  }

  private function createCollectionItem(): CollectionItemView&Stub {
    $collection = $this->createStub(CollectionView::class);
    $collection->method('getUserId')->willReturn(self::OWNER_ID);

    $item = $this->createStub(CollectionItemView::class);
    $item->method('getCollection')->willReturn($collection);

    return $item;
  }

  /**
   * @param string[] $collectionIds
   * @param string[] $sharedCollectionIds
   */
  private function stubContainingCollections(
    TokenInterface $token,
    array $collectionIds,
    array $sharedCollectionIds = [],
    bool $guardAllows = true,
    bool $requireAuthForCollectionShares = false,
  ): void {
    $this->collectionItemRepository->method('getCollectionIdsByImageIds')->willReturn([self::IMAGE_ID => $collectionIds]);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createCollectionItem());

    $shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $shareRepository->method('findByShareable')->willReturnCallback(
      fn (string $shareableId): ?ShareView =>
        in_array($shareableId, $sharedCollectionIds, true) ? $this->createStub(ShareView::class) : null
    );

    $accessGuard = $this->createStub(ShareAccessGuard::class);
    $accessGuard->method('allows')->willReturn($guardAllows);

    $configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
    $configurationProvider->method('get')->willReturn($requireAuthForCollectionShares);

    $collectionVoter = new CollectionScopedImageVoter(
      $shareRepository,
      $this->collectionItemRepository,
      $accessGuard,
      $configurationProvider,
    );

    $this->access->method('isGranted')->willReturnCallback(
      fn (mixed $attribute, mixed $subject = null): bool =>
        $attribute instanceof CollectionScopedImageAccess
        && $collectionVoter->vote($token, $subject, [$attribute]) === VoterInterface::ACCESS_GRANTED
    );
  }

  #[Test]
  public function itGrantsViewToOwnerRegardlessOfVisibility(): void {
    $this->stubImage(self::OWNER_ID);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(self::OWNER_ID), self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsViewToAuthenticatedNonOwnerWhenImageIsPublic(): void {
    $this->stubImage(self::OWNER_ID, isPublic: true);
    $this->stubAccess(guestAllowed: true);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(self::NON_OWNER_ID), self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsViewToAnonymousWhenImageIsPublicAndUnauthenticatedAccessAllowed(): void {
    $this->stubImage(self::OWNER_ID, isPublic: true);
    $this->stubAccess(guestAllowed: true);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesAnonymousWhenImageIsPublicButUnauthenticatedAccessDisabled(): void {
    $this->stubImage(self::OWNER_ID, isPublic: true);
    $this->stubAccess(guestAllowed: false);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesNonOwnerWhenImageIsPrivate(): void {
    $this->stubImage(self::OWNER_ID);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(self::NON_OWNER_ID), self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsViewToShareViewerWhenAnyContainingCollectionIsShared(): void {
    $this->stubImage(self::OWNER_ID);
    $shareViewer = $this->createToken(self::SHARE_VIEWER_ID);
    $this->stubContainingCollections(
      $shareViewer,
      collectionIds: ['collection-1', 'collection-2', 'collection-3'],
      sharedCollectionIds: ['collection-2'],
    );

    $voter = $this->createVoter();

    $result = $voter->vote($shareViewer, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewWhenNoContainingCollectionIsShared(): void {
    $this->stubImage(self::OWNER_ID);
    $nonOwner = $this->createToken(self::NON_OWNER_ID);
    $this->stubContainingCollections(
      $nonOwner,
      collectionIds: ['collection-1', 'collection-2'],
    );

    $voter = $this->createVoter();

    $result = $voter->vote($nonOwner, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesViewWhenShareAccessGuardDenies(): void {
    $this->stubImage(self::OWNER_ID);
    $shareViewer = $this->createToken(self::SHARE_VIEWER_ID);
    $this->stubContainingCollections(
      $shareViewer,
      collectionIds: ['collection-1'],
      sharedCollectionIds: ['collection-1'],
      guardAllows: false,
    );

    $voter = $this->createVoter();

    $result = $voter->vote($shareViewer, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesAnonymousViewWhenAuthRequiredForCollectionShares(): void {
    $this->stubImage(self::OWNER_ID);
    $anonymous = $this->createToken();
    $this->stubContainingCollections(
      $anonymous,
      collectionIds: ['collection-1'],
      sharedCollectionIds: ['collection-1'],
      requireAuthForCollectionShares: true,
    );

    $voter = $this->createVoter();

    $result = $voter->vote($anonymous, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsViewToOwnerWhenImageIsInNoCollections(): void {
    $this->stubImage(self::OWNER_ID);
    $owner = $this->createToken(self::OWNER_ID);
    $this->stubContainingCollections($owner, collectionIds: []);

    $voter = $this->createVoter();

    $result = $voter->vote($owner, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesViewWhenImageIsInNoCollections(): void {
    $this->stubImage(self::OWNER_ID);
    $nonOwner = $this->createToken(self::NON_OWNER_ID);
    $this->stubContainingCollections($nonOwner, collectionIds: []);

    $voter = $this->createVoter();

    $result = $voter->vote($nonOwner, self::IMAGE_ID, [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsCreateToShareViewerWhenContainingCollectionIsShared(): void {
    $this->stubImage(self::OWNER_ID);
    $shareViewer = $this->createToken(self::SHARE_VIEWER_ID);
    $this->stubContainingCollections(
      $shareViewer,
      collectionIds: ['collection-1'],
      sharedCollectionIds: ['collection-1'],
    );

    $voter = $this->createVoter();

    $result = $voter->vote($shareViewer, self::IMAGE_ID, [CommentAccess::Create]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesCreateToNonOwnerWithoutShareAccess(): void {
    $this->stubImage(self::OWNER_ID);
    $nonOwner = $this->createToken(self::NON_OWNER_ID);
    $this->stubContainingCollections(
      $nonOwner,
      collectionIds: ['collection-1', 'collection-2'],
    );

    $voter = $this->createVoter();

    $result = $voter->vote($nonOwner, self::IMAGE_ID, [CommentAccess::Create]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsCreateToAnonymousShareViewerRelyingOnControllerAuthGate(): void {
    $this->stubImage(self::OWNER_ID);
    $anonymous = $this->createToken();
    $this->stubContainingCollections(
      $anonymous,
      collectionIds: ['collection-1'],
      sharedCollectionIds: ['collection-1'],
    );

    $voter = $this->createVoter();

    $result = $voter->vote($anonymous, self::IMAGE_ID, [CommentAccess::Create]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsCreateToOwner(): void {
    $this->stubImage(self::OWNER_ID);

    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(self::OWNER_ID), self::IMAGE_ID, [CommentAccess::Create]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), self::IMAGE_ID, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForNonStringSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [CommentAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }
}
