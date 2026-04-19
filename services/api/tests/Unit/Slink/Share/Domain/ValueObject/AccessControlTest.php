<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Domain\ValueObject;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Share\Domain\ValueObject\AccessControl;
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

    $this->assertSame(['isPublished' => true, 'expiresAt' => null], $payload);
    $this->assertTrue($restored->isPublished);
    $this->assertNull($restored->expiresAt);
  }

  #[Test]
  public function itRoundTripsPayloadWithExpiration(): void {
    $expiresAt = DateTime::fromString('2099-12-31T23:59:59+00:00');
    $accessControl = AccessControl::initial(true)->expireAt($expiresAt);

    $restored = AccessControl::fromPayload($accessControl->toPayload());

    $this->assertTrue($restored->isPublished);
    $this->assertEquals($expiresAt->toString(), $restored->expiresAt?->toString());
  }
}
