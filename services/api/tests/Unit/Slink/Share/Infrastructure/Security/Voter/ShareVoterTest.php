<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\Enum\ShareAccess;
use Slink\Share\Domain\Service\ShareableOwnerResolverInterface;
use Slink\Share\Domain\Share;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\Security\Voter\ShareVoter;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ShareVoterTest extends TestCase {
  private const OWNER_ID = '550e8400-e29b-41d4-a716-446655440000';
  private const OTHER_ID = '660e8400-e29b-41d4-a716-446655440000';
  private const IMAGE_ID = '770e8400-e29b-41d4-a716-446655440000';

  private ShareableOwnerResolverInterface&Stub $ownerResolver;

  protected function setUp(): void {
    parent::setUp();

    $this->ownerResolver = $this->createStub(ShareableOwnerResolverInterface::class);
  }

  #[Test]
  public function itAbstainsForUnsupportedAttribute(): void {
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OWNER_ID), $this->shareView(), ['unknown.attribute']);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OWNER_ID), new \stdClass(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsEditToOwnerOfShareView(): void {
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OWNER_ID), $this->shareView(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsEditToOwnerOfAggregate(): void {
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OWNER_ID), $this->shareAggregate(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesEditToNonOwner(): void {
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OTHER_ID), $this->shareView(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesEditToAnonymousToken(): void {
    $this->ownerResolver->method('resolveOwnerId')->willReturn(self::OWNER_ID);
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(''), $this->shareView(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesWhenOwnerCannotBeResolved(): void {
    $this->ownerResolver->method('resolveOwnerId')->willReturn(null);
    $voter = new ShareVoter($this->ownerResolver);

    $result = $voter->vote($this->token(self::OWNER_ID), $this->shareView(), [ShareAccess::Edit]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  private function token(string $userIdentifier): TokenInterface {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function shareView(): ShareView {
    $share = $this->createStub(ShareView::class);
    $share->method('getShareable')->willReturn(ShareableReference::forImage(ID::fromString(self::IMAGE_ID)));

    return $share;
  }

  private function shareAggregate(): Share {
    $share = $this->createStub(Share::class);
    $share->method('getShareable')->willReturn(ShareableReference::forImage(ID::fromString(self::IMAGE_ID)));

    return $share;
  }
}
