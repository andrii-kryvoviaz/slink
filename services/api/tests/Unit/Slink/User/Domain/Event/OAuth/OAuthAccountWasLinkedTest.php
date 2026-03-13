<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasLinked;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final class OAuthAccountWasLinkedTest extends TestCase {
  #[Test]
  public function itRoundtripsThroughPayload(): void {
    $userId = ID::generate();
    $linkId = ID::generate();
    $linkedAt = DateTime::now();

    $event = new OAuthAccountWasLinked(
      $userId,
      $linkId,
      ProviderSlug::fromString('google'),
      SubjectId::fromString('google-sub-123'),
      Email::fromString('user@example.com'),
      $linkedAt,
    );

    $restored = OAuthAccountWasLinked::fromPayload($event->toPayload());

    $this->assertTrue($restored->userId->equals($userId));
    $this->assertTrue($restored->linkId->equals($linkId));
    $this->assertEquals(ProviderSlug::fromString('google'), $restored->provider);
    $this->assertSame('google-sub-123', $restored->sub->toString());
    $this->assertSame('user@example.com', $restored->email?->toString());
    $this->assertSame($linkedAt->toString(), $restored->linkedAt->toString());
  }

  #[Test]
  public function itHandlesNullableEmail(): void {
    $userId = ID::generate();
    $linkId = ID::generate();
    $linkedAt = DateTime::now();

    $event = new OAuthAccountWasLinked(
      $userId,
      $linkId,
      ProviderSlug::fromString('authentik'),
      SubjectId::fromString('auth-sub-789'),
      null,
      $linkedAt,
    );

    $payload = $event->toPayload();
    $this->assertNull($payload['providerEmail']);

    $restored = OAuthAccountWasLinked::fromPayload($payload);
    $this->assertNull($restored->email);
    $this->assertSame('auth-sub-789', $restored->sub->toString());
  }
}
