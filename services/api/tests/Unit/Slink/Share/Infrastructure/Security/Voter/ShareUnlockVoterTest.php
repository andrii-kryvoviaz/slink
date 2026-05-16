<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Service\ShareableOwnerResolverInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\Security\Voter\ShareUnlockVoter;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ShareUnlockVoterTest extends TestCase {
  private const OWNER_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const OTHER_ID = '660e8400-e29b-41d4-a716-446655440000';
  private const IMAGE_ID = '770e8400-e29b-41d4-a716-446655440000';
  private const COLLECTION_ID = '880e8400-e29b-41d4-a716-446655440000';

  private ShareableOwnerResolverInterface&Stub $ownerResolver;
  private ConfigurationProviderInterface&Stub $configurationProvider;

  protected function setUp(): void {
    parent::setUp();

    $this->ownerResolver = $this->createStub(ShareableOwnerResolverInterface::class);
    $this->configurationProvider = $this->createStub(ConfigurationProviderInterface::class);
  }

  private function createVoter(): ShareUnlockVoter {
    return new ShareUnlockVoter($this->ownerResolver, $this->configurationProvider);
  }

  private function token(string $userIdentifier): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    if ($userIdentifier !== '') {
      $token->method('getUser')->willReturn($this->createStub(UserInterface::class));
    } else {
      $token->method('getUser')->willReturn(null);
    }

    return $token;
  }

  private function imageShareView(): ShareView&Stub {
    $share = $this->createStub(ShareView::class);
    $share->method('getShareable')->willReturn(ShareableReference::forImage(ID::fromString(self::IMAGE_ID)));

    return $share;
  }

  private function collectionShareView(): ShareView&Stub {
    $share = $this->createStub(ShareView::class);
    $share->method('getShareable')->willReturn(ShareableReference::forCollection(ID::fromString(self::COLLECTION_ID)));

    return $share;
  }

  private function imageShareAggregate(): Share {
    $share = $this->createStub(Share::class);
    $share->method('getShareable')->willReturn(ShareableReference::forImage(ID::fromString(self::IMAGE_ID)));

    return $share;
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->imageShareView(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), new \stdClass(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsUnlockToAnonymousOnMediaShareWhenMediaFlagOff(): void {
    $this->configurationProvider->method('get')->willReturn(false);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->imageShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesUnlockToAnonymousOnMediaShareWhenMediaFlagOn(): void {
    $this->configurationProvider
      ->method('get')
      ->willReturnCallback(fn (string $key): bool => $key === 'access.requireAuthForMediaShares');
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->imageShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsUnlockToAnonymousOnCollectionShareWhenCollectionFlagOff(): void {
    $this->configurationProvider->method('get')->willReturn(false);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->collectionShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesUnlockToAnonymousOnCollectionShareWhenCollectionFlagOn(): void {
    $this->configurationProvider
      ->method('get')
      ->willReturnCallback(fn (string $key): bool => $key === 'access.requireAuthForCollectionShares');
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->collectionShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itGrantsUnlockToAuthenticatedNonOwnerEvenWhenMediaFlagOn(): void {
    $this->configurationProvider->method('get')->willReturn(true);
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(self::OTHER_ID), $this->imageShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsUnlockToAuthenticatedNonOwnerEvenWhenCollectionFlagOn(): void {
    $this->configurationProvider->method('get')->willReturn(true);
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(self::OTHER_ID), $this->collectionShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsUnlockToOwnerOfMediaShareEvenWhenMediaFlagOn(): void {
    $this->configurationProvider->method('get')->willReturn(true);
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(self::OWNER_ID), $this->imageShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsUnlockToOwnerOfCollectionShareEvenWhenCollectionFlagOn(): void {
    $this->configurationProvider->method('get')->willReturn(true);
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(self::OWNER_ID), $this->collectionShareView(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itSupportsShareAggregateSubject(): void {
    $this->configurationProvider->method('get')->willReturn(false);
    $voter = $this->createVoter();

    $result = $voter->vote($this->token(''), $this->imageShareAggregate(), [ShareAccess::Unlock]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }
}
