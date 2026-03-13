<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Event\OAuthProvider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\Shared\Infrastructure\Encryption\EncryptionService;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final class OAuthProviderWasCreatedTest extends TestCase {
  private const string ENCRYPTION_SECRET = 'test-encryption-secret';

  protected function setUp(): void {
    EncryptionRegistry::setService(new EncryptionService(self::ENCRYPTION_SECRET));
  }

  #[Test]
  public function itEncryptsSecretsInPayload(): void {
    $event = new OAuthProviderWasCreated(
      ID::fromString('provider-id-1'),
      ProviderName::fromString('Google'),
      ProviderSlug::fromString('google'),
      OAuthType::fromString('oidc'),
      ClientId::fromString('client-id-123'),
      ClientSecret::fromString('client-secret-456'),
      DiscoveryUrl::fromString('https://accounts.google.com/.well-known/openid-configuration'),
      OAuthScopes::fromString('openid profile email'),
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
      ID::fromString('provider-id-1'),
      ProviderName::fromString('Authentik'),
      ProviderSlug::fromString('authentik'),
      OAuthType::fromString('oidc'),
      ClientId::fromString('auth-client-id'),
      ClientSecret::fromString('auth-client-secret'),
      DiscoveryUrl::fromString('https://authentik.example.com/.well-known/openid-configuration'),
      OAuthScopes::fromString('openid profile email'),
      false,
      3.0,
    );

    $restored = OAuthProviderWasCreated::fromPayload($event->toPayload());

    $this->assertEquals($event->id, $restored->id);
    $this->assertEquals($event->name, $restored->name);
    $this->assertEquals($event->slug, $restored->slug);
    $this->assertEquals($event->type, $restored->type);
    $this->assertEquals($event->clientId, $restored->clientId);
    $this->assertEquals($event->clientSecret, $restored->clientSecret);
    $this->assertEquals($event->discoveryUrl, $restored->discoveryUrl);
    $this->assertEquals($event->scopes, $restored->scopes);
    $this->assertSame($event->enabled, $restored->enabled);
    $this->assertSame($event->sortOrder, $restored->sortOrder);
  }
}
