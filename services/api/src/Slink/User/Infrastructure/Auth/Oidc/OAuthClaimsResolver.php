<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\IdToken;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\TokenClaims;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final readonly class OAuthClaimsResolver {
  public function __construct(
    private IdTokenVerifier $idTokenVerifier,
    private OidcDiscoveryInterface $oidcDiscovery,
    private LoggerInterface $logger,
  ) {}

  public function resolve(GenericProvider $client, AccessToken $accessToken, OAuthProviderView $provider): OAuthIdentity {
    $tokenClaims = $this->extractTokenClaims($client, $accessToken, $provider);

    return OAuthIdentity::fromTokenClaims($tokenClaims, OAuthProvider::from($provider->getSlug()));
  }

  private function extractTokenClaims(GenericProvider $client, AccessToken $accessToken, OAuthProviderView $provider): TokenClaims {
    $idToken = $this->extractIdToken($accessToken);

    if ($idToken === null) {
      return TokenClaims::fromPayload($client->getResourceOwner($accessToken)->toArray());
    }

    try {
      $discovery = $this->oidcDiscovery->discover($provider->getDiscoveryUrl());

      return $this->idTokenVerifier->verify(
        $idToken,
        $discovery->getJwksUri(),
        $discovery->getIssuer(),
        ClientId::fromString($provider->getClientId()),
      );
    } catch (\Throwable $e) {
      $this->logger->warning('ID token verification failed, falling back to userinfo endpoint', [
        'provider' => $provider->getSlug(),
        'error' => $e->getMessage(),
      ]);

      return TokenClaims::fromPayload($client->getResourceOwner($accessToken)->toArray());
    }
  }

  private function extractIdToken(AccessToken $accessToken): ?IdToken {
    $values = $accessToken->getValues();
    $raw = $values['id_token'] ?? null;

    return is_string($raw) && $raw !== '' ? IdToken::fromString($raw) : null;
  }
}
