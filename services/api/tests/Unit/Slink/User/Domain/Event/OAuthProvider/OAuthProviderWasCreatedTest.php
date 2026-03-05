<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;

final class OAuthProviderWasCreatedTest extends TestCase {
  private const string ENCRYPTION_SECRET = 'test-encryption-secret';

  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService(self::ENCRYPTION_SECRET));
  }

  #[Test]
  public function itEncryptsSecretsInPayload(): void {
    $event = new OAuthProviderWasCreated(
      'provider-id-1',
      'Google',
      'google',
      'oidc',
      'client-id-123',
      'client-secret-456',
      'https://accounts.google.com/.well-known/openid-configuration',
      'openid profile email',
      true,
      1.0,
    );

    $payload = $event->toPayload();

    $this->assertNotSame('client-id-123', $payload['clientId']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientId']);
    $this->assertNotSame('client-secret-456', $payload['clientSecret']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientSecret']);
  }

  #[Test]
  public function itRoundtripsThroughPayload(): void {
    $event = new OAuthProviderWasCreated(
      'provider-id-1',
      'Authentik',
      'authentik',
      'oauth2',
      'auth-client-id',
      'auth-client-secret',
      'https://authentik.example.com/.well-known/openid-configuration',
      'openid profile email',
      false,
      3.0,
    );

    $restored = OAuthProviderWasCreated::fromPayload($event->toPayload());

    $this->assertSame($event->id, $restored->id);
    $this->assertSame($event->name, $restored->name);
    $this->assertSame($event->slug, $restored->slug);
    $this->assertSame($event->type, $restored->type);
    $this->assertSame($event->clientId, $restored->clientId);
    $this->assertSame($event->clientSecret, $restored->clientSecret);
    $this->assertSame($event->discoveryUrl, $restored->discoveryUrl);
    $this->assertSame($event->scopes, $restored->scopes);
    $this->assertSame($event->enabled, $restored->enabled);
    $this->assertSame($event->sortOrder, $restored->sortOrder);
  }
}
