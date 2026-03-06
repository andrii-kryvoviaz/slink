<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\ValueObject\OAuth;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\ValueObject\OAuth\OAuthContext;
use Slink\User\Domain\ValueObject\OAuth\PkceVerifier;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final class OAuthContextTest extends TestCase {

  #[Test]
  public function itCreatesViaFactory(): void {
    $provider = OAuthProvider::Google;
    $redirectUri = RedirectUri::fromString('https://example.com/callback');
    $pkceVerifier = PkceVerifier::fromString('verifier-value');

    $context = OAuthContext::create($provider, $redirectUri, $pkceVerifier);

    $this->assertInstanceOf(OAuthContext::class, $context);
    $this->assertSame($provider, $context->getProvider());
    $this->assertSame($redirectUri, $context->getRedirectUri());
    $this->assertSame($pkceVerifier, $context->getPkceVerifier());
  }

  #[Test]
  public function itRoundtripsViaPayload(): void {
    $original = OAuthContext::create(
      OAuthProvider::Keycloak,
      RedirectUri::fromString('https://app.test/auth/callback'),
      PkceVerifier::fromString('pkce-123'),
    );

    $restored = OAuthContext::fromPayload($original->toPayload());

    $this->assertSame($original->getProvider(), $restored->getProvider());
    $this->assertSame($original->getRedirectUri()->toString(), $restored->getRedirectUri()->toString());
    $this->assertNotNull($restored->getPkceVerifier());
    $this->assertSame($original->getPkceVerifier()?->toString(), $restored->getPkceVerifier()->toString());
  }

  #[Test]
  public function itHandlesNullablePkceVerifier(): void {
    $context = OAuthContext::create(
      OAuthProvider::Authelia,
      RedirectUri::fromString('https://example.com/cb'),
      null,
    );

    $this->assertNull($context->getPkceVerifier());

    $payload = $context->toPayload();
    $this->assertNull($payload['pkceVerifier']);

    $restored = OAuthContext::fromPayload($payload);
    $this->assertNull($restored->getPkceVerifier());
  }
}
