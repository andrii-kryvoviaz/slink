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
  public function itSerializesToPayload(): void {
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

    $this->assertSame('provider-id-1', $payload['id']);
    $this->assertSame('Google', $payload['name']);
    $this->assertSame('google', $payload['slug']);
    $this->assertSame('oidc', $payload['type']);
    $this->assertNotSame('client-id-123', $payload['clientId']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientId']);
    $this->assertNotSame('client-secret-456', $payload['clientSecret']);
    $this->assertStringStartsWith('enc:v1:', $payload['clientSecret']);
    $this->assertSame('https://accounts.google.com/.well-known/openid-configuration', $payload['discoveryUrl']);
    $this->assertSame('openid profile email', $payload['scopes']);
    $this->assertTrue($payload['enabled']);
    $this->assertSame(1.0, $payload['sortOrder']);
  }

  #[Test]
  public function itDeserializesFromPayload(): void {
    $original = new OAuthProviderWasCreated(
      'provider-id-1',
      'Google',
      'google',
      'oidc',
      'client-id-123',
      'client-secret-456',
      'https://accounts.google.com/.well-known/openid-configuration',
      'openid profile email',
      true,
      2.5,
    );

    $payload = $original->toPayload();
    $restored = OAuthProviderWasCreated::fromPayload($payload);

    $this->assertSame('provider-id-1', $restored->id);
    $this->assertSame('Google', $restored->name);
    $this->assertSame('google', $restored->slug);
    $this->assertSame('oidc', $restored->type);
    $this->assertSame('client-id-123', $restored->clientId);
    $this->assertSame('client-secret-456', $restored->clientSecret);
    $this->assertSame('https://accounts.google.com/.well-known/openid-configuration', $restored->discoveryUrl);
    $this->assertSame('openid profile email', $restored->scopes);
    $this->assertTrue($restored->enabled);
    $this->assertSame(2.5, $restored->sortOrder);
  }

  #[Test]
  public function itRoundtripsThroughPayload(): void {
    $event = new OAuthProviderWasCreated(
      'provider-id-1',
      'Github',
      'github',
      'oauth2',
      'gh-client-id',
      'gh-client-secret',
      'https://github.com/.well-known/openid-configuration',
      'read:user user:email',
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
