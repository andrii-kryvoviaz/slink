<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;

final class OAuthProviderWasUpdatedTest extends TestCase {
  private const string ENCRYPTION_SECRET = 'test-encryption-secret';

  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService(self::ENCRYPTION_SECRET));
  }

  #[Test]
  public function itEncryptsSecretsInPayload(): void {
    $event = new OAuthProviderWasUpdated(
      'provider-id-1',
      clientId: 'new-client-id',
      clientSecret: 'new-client-secret',
    );

    $payload = $event->toPayload();

    $this->assertArrayHasKey('clientId', $payload);
    $this->assertArrayHasKey('clientSecret', $payload);
    $this->assertNotSame('new-client-id', $payload['clientId']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientId']);
    $this->assertNotSame('new-client-secret', $payload['clientSecret']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientSecret']);
  }

  #[Test]
  public function itRoundtripsThroughPayloadWithPartialFields(): void {
    $event = new OAuthProviderWasUpdated(
      'provider-id-1',
      name: 'Updated Google',
      enabled: false,
    );

    $payload = $event->toPayload();

    $this->assertArrayNotHasKey('slug', $payload);
    $this->assertArrayNotHasKey('type', $payload);
    $this->assertArrayNotHasKey('clientId', $payload);
    $this->assertArrayNotHasKey('clientSecret', $payload);
    $this->assertArrayNotHasKey('discoveryUrl', $payload);
    $this->assertArrayNotHasKey('scopes', $payload);
    $this->assertArrayNotHasKey('sortOrder', $payload);

    $restored = OAuthProviderWasUpdated::fromPayload($payload);

    $this->assertSame('provider-id-1', $restored->id);
    $this->assertSame('Updated Google', $restored->name);
    $this->assertFalse($restored->enabled);
    $this->assertNull($restored->slug);
    $this->assertNull($restored->type);
    $this->assertNull($restored->clientId);
    $this->assertNull($restored->clientSecret);
    $this->assertNull($restored->discoveryUrl);
    $this->assertNull($restored->scopes);
    $this->assertNull($restored->sortOrder);
  }
}
