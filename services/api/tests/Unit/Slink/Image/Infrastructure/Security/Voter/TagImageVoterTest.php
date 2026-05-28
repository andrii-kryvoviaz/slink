<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Infrastructure\Security\Voter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Image\Infrastructure\Security\Voter\TagImageVoter;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class TagImageVoterTest extends TestCase {
  private function createVoter(): TagImageVoter {
    return new TagImageVoter();
  }

  private function createToken(string $userIdentifier = ''): TokenInterface&Stub {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn($userIdentifier);

    return $token;
  }

  private function createImageView(?string $ownerId): ImageView&Stub {
    $userId = null;

    if ($ownerId !== null) {
      $userId = ID::fromString($ownerId);
    }

    $image = $this->createStub(ImageView::class);
    $image->method('getUserId')->willReturn($userId);

    return $image;
  }

  #[Test]
  public function itAbstainsForNonTagAttribute(): void {
    $voter = $this->createVoter();
    $image = $this->createImageView('550e8400-e29b-41d4-a716-446655440000');

    $result = $voter->vote($this->createToken(), $image, [ImageAccess::View]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itAbstainsForUnsupportedSubject(): void {
    $voter = $this->createVoter();

    $result = $voter->vote($this->createToken(), new \stdClass(), [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
  }

  #[Test]
  public function itGrantsTagOnOrphanImageToAuthenticatedUser(): void {
    $voter = $this->createVoter();
    $image = $this->createImageView(null);

    $result = $voter->vote($this->createToken('660e8400-e29b-41d4-a716-446655440000'), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsTagOnOrphanImageToAnonymousUser(): void {
    $voter = $this->createVoter();
    $image = $this->createImageView(null);

    $result = $voter->vote($this->createToken(), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itGrantsTagToOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($ownerId), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_GRANTED, $result);
  }

  #[Test]
  public function itDeniesTagToNonOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';
    $requesterId = '660e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken($requesterId), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }

  #[Test]
  public function itDeniesTagToAnonymousUserWhenImageHasOwner(): void {
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $voter = $this->createVoter();
    $image = $this->createImageView($ownerId);

    $result = $voter->vote($this->createToken(), $image, [ImageAccess::Tag]);

    $this->assertSame(VoterInterface::ACCESS_DENIED, $result);
  }
}
