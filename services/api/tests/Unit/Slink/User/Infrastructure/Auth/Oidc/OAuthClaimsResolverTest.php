<?php

declare(strict_types=1);

namespace Unit\Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Signature\JWSVerifier;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Slink\User\Domain\Exception\MissingIdTokenException;
use Slink\User\Infrastructure\Auth\Oidc\JwksProvider;
use Slink\User\Infrastructure\Auth\Oidc\JwsParser;
use Slink\User\Infrastructure\Auth\Oidc\OAuthClaimsResolver;
use Slink\User\Infrastructure\Auth\Oidc\OidcDiscoveryInterface;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final class OAuthClaimsResolverTest extends TestCase {

  #[Test]
  public function itThrowsWhenIdTokenIsMissing(): void {
    $accessToken = $this->createStub(AccessToken::class);
    $accessToken->method('getValues')->willReturn([]);

    $jwsParser = $this->createStub(JwsParser::class);
    $jwksProvider = $this->createStub(JwksProvider::class);
    $jwsVerifier = $this->createStub(JWSVerifier::class);
    $claimCheckerManager = $this->createStub(ClaimCheckerManager::class);
    $oidcDiscovery = $this->createStub(OidcDiscoveryInterface::class);

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('warning');

    $provider = $this->createStub(OAuthProviderView::class);
    $provider->method('getSlug')->willReturn(ProviderSlug::fromString('google'));

    $client = $this->createStub(GenericProvider::class);

    $resolver = new OAuthClaimsResolver(
      $jwsParser,
      $jwksProvider,
      $jwsVerifier,
      $claimCheckerManager,
      $oidcDiscovery,
      $logger,
    );

    $this->expectException(MissingIdTokenException::class);

    $resolver->resolve($client, $accessToken, $provider);
  }
}
