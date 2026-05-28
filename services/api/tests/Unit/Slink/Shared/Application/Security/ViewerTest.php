<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Shared\Application\Security;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Application\Security\Viewer;
use Slink\Shared\Domain\Contract\OwnerAwareInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class ViewerTest extends TestCase {
  #[Test]
  public function itResolvesAuthenticatedTokenToUserId(): void {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn('11111111-1111-1111-1111-111111111111');

    $viewer = Viewer::fromToken($token);

    $this->assertFalse($viewer->isAnonymous());
    $this->assertTrue($viewer->userId?->equals(ID::fromString('11111111-1111-1111-1111-111111111111')));
  }

  #[Test]
  public function itResolvesIdentifierStringToUserId(): void {
    $viewer = Viewer::fromIdentifier('11111111-1111-1111-1111-111111111111');

    $this->assertFalse($viewer->isAnonymous());
    $this->assertTrue($viewer->userId?->equals(ID::fromString('11111111-1111-1111-1111-111111111111')));
  }

  #[Test]
  public function itTreatsNullIdentifierAsAnonymous(): void {
    $viewer = Viewer::fromIdentifier(null);

    $this->assertTrue($viewer->isAnonymous());
  }

  #[Test]
  public function itTreatsEmptyIdentifierAsAnonymous(): void {
    $viewer = Viewer::fromIdentifier('');

    $this->assertTrue($viewer->isAnonymous());
  }

  #[Test]
  public function itTreatsEmptyTokenIdentifierAsAnonymous(): void {
    $token = $this->createStub(TokenInterface::class);
    $token->method('getUserIdentifier')->willReturn('');

    $viewer = Viewer::fromToken($token);

    $this->assertTrue($viewer->isAnonymous());
    $this->assertNull($viewer->userId);
  }

  #[Test]
  public function itDelegatesOwnsToSubject(): void {
    $userId = ID::generate();
    $viewer = new Viewer($userId);

    $subject = $this->createMock(OwnerAwareInterface::class);
    $subject
      ->expects($this->once())
      ->method('isOwnedBy')
      ->with($userId)
      ->willReturn(true);

    $this->assertTrue($viewer->owns($subject));
  }

  #[Test]
  public function ownsReturnsFalseWhenSubjectDoesNotImplementInterface(): void {
    $viewer = new Viewer(ID::generate());

    $this->assertFalse($viewer->owns(new \stdClass()));
  }

  #[Test]
  public function anonymousViewerPassesNullToSubject(): void {
    $viewer = new Viewer(null);

    $subject = $this->createMock(OwnerAwareInterface::class);
    $subject
      ->expects($this->once())
      ->method('isOwnedBy')
      ->with(null)
      ->willReturn(false);

    $this->assertFalse($viewer->owns($subject));
  }
}
