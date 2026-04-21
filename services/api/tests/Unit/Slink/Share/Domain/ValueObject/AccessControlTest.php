<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\HashedSharePassword;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final class AccessControlTest extends TestCase {
  #[Test]
  public function itInitialisesPublishedAndUnbounded(): void {
    $accessControl = AccessControl::initial(true);

    $this->assertTrue($accessControl->isPublished);
    $this->assertNull($accessControl->expiresAt);
  }

  #[Test]
  public function itInitialisesUnpublishedAndUnbounded(): void {
    $accessControl = AccessControl::initial(false);

    $this->assertFalse($accessControl->isPublished);
    $this->assertNull($accessControl->expiresAt);
  }

  #[Test]
  public function itPublishesWithoutTouchingExpiration(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(false)->expireAt($expiresAt);

    $next = $accessControl->publish();

    $this->assertNotSame($accessControl, $next);
    $this->assertTrue($next->isPublished);
    $this->assertEquals($expiresAt->toString(), $next->expiresAt?->toString());
  }

  #[Test]
  public function itReturnsSameInstanceWhenPublishingAlreadyPublished(): void {
    $accessControl = AccessControl::initial(true);

    $next = $accessControl->publish();

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itUnpublishesWithoutTouchingExpirationOrPassword(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $password = HashedSharePassword::encode('hunter2');
    $accessControl = AccessControl::initial(true)
      ->expireAt($expiresAt)
      ->withPassword($password);

    $next = $accessControl->unpublish();

    $this->assertNotSame($accessControl, $next);
    $this->assertFalse($next->isPublished);
    $this->assertEquals($expiresAt->toString(), $next->expiresAt?->toString());
    $this->assertSame($password->toString(), $next->passwordHash);
  }

  #[Test]
  public function itReturnsSameInstanceWhenUnpublishingAlreadyUnpublished(): void {
    $accessControl = AccessControl::initial(false);

    $next = $accessControl->unpublish();

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itSetsExpirationWithoutTouchingPublication(): void {
    $accessControl = AccessControl::initial(true);
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');

    $next = $accessControl->expireAt($expiresAt);

    $this->assertNotSame($accessControl, $next);
    $this->assertTrue($next->isPublished);
    $this->assertEquals($expiresAt->toString(), $next->expiresAt?->toString());
  }

  #[Test]
  public function itIsIdempotentWhenSettingSameExpirationTwice(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)->expireAt($expiresAt);

    $next = $accessControl->expireAt(DateTime::fromString('2099-12-31T23:59:59+00:00'));

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itIsIdempotentWhenClearingAlreadyNullExpiration(): void {
    $accessControl = AccessControl::initial(true);

    $next = $accessControl->expireAt(null);

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itClearsExpiration(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)->expireAt($expiresAt);

    $next = $accessControl->expireAt(null);

    $this->assertNotSame($accessControl, $next);
    $this->assertNull($next->expiresAt);
    $this->assertTrue($next->isPublished);
  }

  #[Test]
  public function itRoundTripsPayloadWithoutExpiration(): void {
    $accessControl = AccessControl::initial(true);

    $payload = $accessControl->toPayload();
    $restored = AccessControl::fromPayload($payload);

    $this->assertSame(['isPublished' => true, 'expiresAt' => null, 'passwordHash' => null], $payload);
    $this->assertTrue($restored->isPublished);
    $this->assertNull($restored->expiresAt);
    $this->assertNull($restored->passwordHash);
  }

  #[Test]
  public function itRoundTripsPayloadWithExpiration(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)->expireAt($expiresAt);

    $restored = AccessControl::fromPayload($accessControl->toPayload());

    $this->assertTrue($restored->isPublished);
    $this->assertEquals($expiresAt->toString(), $restored->expiresAt?->toString());
  }

  #[Test]
  public function itSetsPasswordWithoutTouchingPublicationOrExpiration(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)->expireAt($expiresAt);
    $password = HashedSharePassword::encode('hunter2');

    $next = $accessControl->withPassword($password);

    $this->assertNotSame($accessControl, $next);
    $this->assertTrue($next->isPublished);
    $this->assertEquals($expiresAt->toString(), $next->expiresAt?->toString());
    $this->assertSame($password->toString(), $next->passwordHash);
  }

  #[Test]
  public function itIsIdempotentWhenSettingSamePasswordHashTwice(): void {
    $password = HashedSharePassword::encode('hunter2');
    $accessControl = AccessControl::initial(true)->withPassword($password);

    $next = $accessControl->withPassword(HashedSharePassword::fromHash($password->toString()));

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itIsIdempotentWhenClearingAlreadyNullPassword(): void {
    $accessControl = AccessControl::initial(true);

    $next = $accessControl->withPassword(null);

    $this->assertSame($accessControl, $next);
  }

  #[Test]
  public function itClearsPassword(): void {
    $password = HashedSharePassword::encode('hunter2');
    $accessControl = AccessControl::initial(true)->withPassword($password);

    $next = $accessControl->withPassword(null);

    $this->assertNotSame($accessControl, $next);
    $this->assertNull($next->passwordHash);
    $this->assertTrue($next->isPublished);
  }

  #[Test]
  public function itRoundTripsPayloadWithPassword(): void {
    $password = HashedSharePassword::encode('hunter2');
    $accessControl = AccessControl::initial(true)->withPassword($password);

    $restored = AccessControl::fromPayload($accessControl->toPayload());

    $this->assertTrue($restored->isPublished);
    $this->assertSame($password->toString(), $restored->passwordHash);

    $restoredPassword = $restored->getPassword();
    $this->assertNotNull($restoredPassword);
    $this->assertTrue($restoredPassword->match('hunter2'));
  }

  #[Test]
  public function matchesPasswordReturnsTrueWhenBothAreNull(): void {
    $accessControl = AccessControl::initial(true);

    $this->assertTrue($accessControl->matchesPassword(null));
  }

  #[Test]
  public function matchesPasswordReturnsFalseWhenClearingAgainstExistingHash(): void {
    $accessControl = AccessControl::initial(true)
      ->withPassword(HashedSharePassword::encode('hunter2'));

    $this->assertFalse($accessControl->matchesPassword(null));
  }

  #[Test]
  public function matchesPasswordReturnsFalseWhenPlaintextGivenButNoneStored(): void {
    $accessControl = AccessControl::initial(true);

    $this->assertFalse($accessControl->matchesPassword('hunter2'));
  }

  #[Test]
  public function matchesPasswordReturnsTrueWhenPlaintextMatchesStoredHash(): void {
    $accessControl = AccessControl::initial(true)
      ->withPassword(HashedSharePassword::encode('hunter2'));

    $this->assertTrue($accessControl->matchesPassword('hunter2'));
  }

  #[Test]
  public function matchesPasswordReturnsFalseWhenPlaintextDiffersFromStoredHash(): void {
    $accessControl = AccessControl::initial(true)
      ->withPassword(HashedSharePassword::encode('hunter2'));

    $this->assertFalse($accessControl->matchesPassword('different'));
  }
}
