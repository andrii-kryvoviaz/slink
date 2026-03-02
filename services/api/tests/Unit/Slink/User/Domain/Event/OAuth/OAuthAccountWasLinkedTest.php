<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Event\OAuth\OAuthAccountWasLinked;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\SubjectId;

final class OAuthAccountWasLinkedTest extends TestCase {
  #[Test]
  public function itSerializesAllFieldsToPayload(): void {
    $userId = ID::generate();
    $linkId = ID::generate();
    $linkedAt = DateTime::now();

    $event = new OAuthAccountWasLinked(
      $userId,
      $linkId,
      OAuthProvider::Google,
      SubjectId::fromString('google-sub-123'),
      Email::fromString('user@example.com'),
      $linkedAt,
    );

    $payload = $event->toPayload();

    $this->assertSame($userId->toString(), $payload['userId']);
    $this->assertSame($linkId->toString(), $payload['linkId']);
    $this->assertSame('google', $payload['providerSlug']);
    $this->assertSame('google-sub-123', $payload['providerUserId']);
    $this->assertSame('user@example.com', $payload['providerEmail']);
    $this->assertSame($linkedAt->toString(), $payload['linkedAt']);
  }

  #[Test]
  public function itDeserializesAndReconstructsValueObjects(): void {
    $userId = ID::generate();
    $linkId = ID::generate();
    $linkedAt = DateTime::now();

    $payload = [
      'userId' => $userId->toString(),
      'linkId' => $linkId->toString(),
      'providerSlug' => 'keycloak',
      'providerUserId' => 'kc-sub-456',
      'providerEmail' => 'dev@example.com',
      'linkedAt' => $linkedAt->toString(),
    ];

    $event = OAuthAccountWasLinked::fromPayload($payload);

    $this->assertTrue($event->userId->equals($userId));
    $this->assertTrue($event->linkId->equals($linkId));
    $this->assertSame(OAuthProvider::Keycloak, $event->provider);
    $this->assertSame('kc-sub-456', $event->sub->toString());
    $this->assertSame('dev@example.com', $event->email?->toString());
    $this->assertSame($linkedAt->toString(), $event->linkedAt->toString());
  }

  #[Test]
  public function itHandlesNullableEmail(): void {
    $userId = ID::generate();
    $linkId = ID::generate();
    $linkedAt = DateTime::now();

    $event = new OAuthAccountWasLinked(
      $userId,
      $linkId,
      OAuthProvider::Authentik,
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
