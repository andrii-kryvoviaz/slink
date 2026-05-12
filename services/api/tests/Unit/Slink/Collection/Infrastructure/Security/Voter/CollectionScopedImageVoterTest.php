<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Collection\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionItemView;
use Slink\Collection\Infrastructure\Security\Voter\CollectionScopedImageVoter;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Application\Service\ShareAccessGuard;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Exception\ShareExpiredException;
use Slink\Share\Domain\Exception\SharePasswordRequiredException;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class CollectionScopedImageVoterTest extends TestCase {
  private ImageUrlSignatureInterface&Stub $signature;
  private ShareRepositoryInterface&Stub $shareRepository;
  private CollectionItemRepositoryInterface&Stub $collectionItemRepository;
  private ShareAccessGuard $accessGuard;

  protected function setUp(): void {
    parent::setUp();

    $this->signature = $this->createStub(ImageUrlSignatureInterface::class);
    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->collectionItemRepository = $this->createStub(CollectionItemRepositoryInterface::class);
    $this->accessGuard = $this->createStub(ShareAccessGuard::class);
  }

  private function createVoter(): CollectionScopedImageVoter {
    return new CollectionScopedImageVoter(
      $this->signature,
      $this->shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
    );
  }

  private function createToken(): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn('');

    return $token;
  }

  private function createContext(
    string $imageId,
    ?string $scopeCollectionId = null,
    ?string $scopeSignature = null,
    ?string $ownerId = null,
  ): ImageAccessContext {
    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($imageId);
    $image
      ->method('isOwnedBy')
      ->willReturnCallback(fn (?ID $userId): bool => $ownerId !== null && $userId?->equals(ID::fromString($ownerId)) === true);

    return new ImageAccessContext($image, $scopeCollectionId, $scopeSignature);
  }

  private function createAuthenticatedToken(string $userIdentifier): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'col', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itDeniesWhenCollectionParamMissing(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', null, 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenCollectionParamEmpty(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', '', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureParamMissing(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', null);

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureParamEmpty(): void {
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', '');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenSignatureInvalid(): void {
    $this->signature->method('verify')->willReturn(false);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'bad');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenShareMissing(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn(null);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenImageNotInCollection(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn(null);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenShareAccessGuardRejects(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itPropagatesPasswordRequiredException(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willThrowException(new SharePasswordRequiredException('share-id'));

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $this->expectException(SharePasswordRequiredException::class);

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);
  }

  #[Test]
  public function itPropagatesShareExpiredException(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willThrowException(new ShareExpiredException());

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $this->expectException(ShareExpiredException::class);

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);
  }

  #[Test]
  public function itGrantsWhenAllChecksPass(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(true);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itUsesShareableTypeCollectionForLookup(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->accessGuard->method('allows')->willReturn(true);

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('findByShareable')
      ->with('collection-id', ShareableType::Collection)
      ->willReturn($this->createStub(ShareView::class));

    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));

    $voter = new CollectionScopedImageVoter(
      $this->signature,
      $shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
    );

    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);
  }

  #[Test]
  public function itBindsSignatureToImageIdAndCollectionId(): void {
    $signature = $this->createMock(ImageUrlSignatureInterface::class);
    $signature
      ->expects($this->once())
      ->method('verify')
      ->with('image-id', ['collection' => 'collection-id'], 'sig')
      ->willReturn(true);

    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(true);

    $voter = new CollectionScopedImageVoter(
      $signature,
      $this->shareRepository,
      $this->collectionItemRepository,
      $this->accessGuard,
    );

    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itLooksUpItemMembershipWithCollectionAndImageId(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->accessGuard->method('allows')->willReturn(true);

    $collectionItemRepository = $this->createMock(CollectionItemRepositoryInterface::class);
    $collectionItemRepository
      ->expects($this->once())
      ->method('findByCollectionAndItemId')
      ->with('collection-id', 'image-id')
      ->willReturn($this->createStub(CollectionItemView::class));

    $voter = new CollectionScopedImageVoter(
      $this->signature,
      $this->shareRepository,
      $collectionItemRepository,
      $this->accessGuard,
    );

    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);
  }

  #[Test]
  public function itInvokesAccessGuardWithShareReturnedByRepository(): void {
    $this->signature->method('verify')->willReturn(true);
    $share = $this->createStub(ShareView::class);
    $this->shareRepository->method('findByShareable')->willReturn($share);
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));

    $captured = [];
    $this->accessGuard
      ->method('allows')
      ->willReturnCallback(function (object $subject) use (&$captured): bool {
        $captured[] = $subject;
        return true;
      });

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertCount(1, $captured);
    $this->assertSame($share, $captured[0]);
  }

  /**
   * @return array<string, array{?string, ?string, bool, bool}>
   */
  public static function denyPathsThatMustNotInvokeGuard(): array {
    return [
      'missing collection'  => [null, 'sig', false, false],
      'empty collection'    => ['',   'sig', false, false],
      'missing signature'   => ['collection-id', null, false, false],
      'empty signature'     => ['collection-id', '',   false, false],
      'invalid signature'   => ['collection-id', 'bad', false, false],
      'share missing'       => ['collection-id', 'sig', true,  false],
      'item not in share'   => ['collection-id', 'sig', true,  true],
    ];
  }

  #[Test]
  #[\PHPUnit\Framework\Attributes\DataProvider('denyPathsThatMustNotInvokeGuard')]
  public function itNeverInvokesAccessGuardOnCheapDenyPaths(
    ?string $collectionId,
    ?string $signatureValue,
    bool $signatureValid,
    bool $hasShare,
  ): void {
    $this->signature->method('verify')->willReturn($signatureValid);
    $this->shareRepository->method('findByShareable')->willReturn(
      $hasShare ? $this->createStub(ShareView::class) : null,
    );
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn(null);

    $guardCalls = 0;
    $this->accessGuard
      ->method('allows')
      ->willReturnCallback(function () use (&$guardCalls): bool {
        $guardCalls++;
        return true;
      });

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', $collectionId, $signatureValue);

    $result = $voter->vote($this->createToken(), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
    $this->assertSame(0, $guardCalls, 'ShareAccessGuard must not be invoked on cheap-deny paths');
  }

  #[Test]
  public function itProducesSameVerdictForAnonymousAndNonOwnerTokens(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(true);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', '11111111-1111-1111-1111-111111111111');

    $anonymousToken = $this->createAuthenticatedToken('');
    $nonOwnerToken = $this->createAuthenticatedToken('22222222-2222-2222-2222-222222222222');

    $this->assertSame(
      $voter->vote($anonymousToken, $context, [ImageAccess::View]),
      $voter->vote($nonOwnerToken, $context, [ImageAccess::View]),
    );
  }

  #[Test]
  public function itGrantsImageOwnerEvenWhenGuardWouldDeny(): void {
    $this->accessGuard->method('allows')->willReturn(false);

    $ownerId = '11111111-1111-1111-1111-111111111111';
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', $ownerId);

    $result = $voter->vote($this->createAuthenticatedToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsImageOwnerEvenWhenGuardWouldThrowPasswordRequired(): void {
    $this->accessGuard->method('allows')->willThrowException(new SharePasswordRequiredException('share-id'));

    $ownerId = '11111111-1111-1111-1111-111111111111';
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', $ownerId);

    $result = $voter->vote($this->createAuthenticatedToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsImageOwnerEvenWhenGuardWouldThrowExpired(): void {
    $this->accessGuard->method('allows')->willThrowException(new ShareExpiredException());

    $ownerId = '11111111-1111-1111-1111-111111111111';
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', $ownerId);

    $result = $voter->vote($this->createAuthenticatedToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDoesNotInvokeGuardForImageOwner(): void {
    $guardCalls = 0;
    $this->accessGuard
      ->method('allows')
      ->willReturnCallback(function () use (&$guardCalls): bool {
        $guardCalls++;
        return true;
      });

    $ownerId = '11111111-1111-1111-1111-111111111111';
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', $ownerId);

    $voter->vote($this->createAuthenticatedToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(0, $guardCalls);
  }

  #[Test]
  public function itGrantsImageOwnerEvenWithMissingScopeParams(): void {
    $ownerId = '11111111-1111-1111-1111-111111111111';
    $voter = $this->createVoter();
    $context = $this->createContext('image-id', null, null, $ownerId);

    $result = $voter->vote($this->createAuthenticatedToken($ownerId), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDoesNotBypassWhenImageOwnerIsNull(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig');

    $result = $voter->vote($this->createAuthenticatedToken('11111111-1111-1111-1111-111111111111'), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDoesNotBypassWhenTokenIdentifierIsEmpty(): void {
    $this->signature->method('verify')->willReturn(true);
    $this->shareRepository->method('findByShareable')->willReturn($this->createStub(ShareView::class));
    $this->collectionItemRepository->method('findByCollectionAndItemId')->willReturn($this->createStub(CollectionItemView::class));
    $this->accessGuard->method('allows')->willReturn(false);

    $voter = $this->createVoter();
    $context = $this->createContext('image-id', 'collection-id', 'sig', '11111111-1111-1111-1111-111111111111');

    $result = $voter->vote($this->createAuthenticatedToken(''), $context, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }
}
