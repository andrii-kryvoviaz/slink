<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use League\OAuth2\Client\Token\AccessToken;
use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\Contracts\OAuthStateManagerInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthContext;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Slink\User\Domain\ValueObject\OAuth\PkceVerifier;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final readonly class OAuthAdapter implements OAuthAdapterInterface {
  public function __construct(
    private OAuthClientFactoryInterface $clientFactory,
    private OAuthStateManagerInterface $stateManager,
    private OAuthClaimsResolver $claimsResolver,
    private OAuthProviderRepositoryInterface $providerRepository,
  ) {}

  #[\Override]
  public function getAuthorizationUrl(OAuthProviderProfile $provider, RedirectUri $redirectUri): string {
    $oauthClient = $this->clientFactory->create($provider, $redirectUri);

    $authorizationUrl = $oauthClient->getAuthorizationUrl([
      'scope' => $provider->getScopes(),
    ]);

    $context = OAuthContext::create(
      OAuthProvider::from($provider->getSlug()),
      $redirectUri,
      PkceVerifier::fromString($oauthClient->getPkceCode()),
    );

    $this->stateManager->storeState(
      OAuthState::fromString($oauthClient->getState()),
      $context,
    );

    return $authorizationUrl;
  }

  #[\Override]
  public function exchangeCode(AuthorizationCode $code, OAuthState $state): OAuthIdentity {
    $context = $this->stateManager->consume($state);
    $provider = $this->providerRepository->findByProvider($context->getProvider());

    if (!$provider?->isEnabled()) {
      throw new InvalidCredentialsException();
    }

    $oauthClient = $this->clientFactory->create($provider, $context->getRedirectUri());

    $pkceVerifier = $context->getPkceVerifier();
    if ($pkceVerifier !== null) {
      $oauthClient->setPkceCode($pkceVerifier->toString());
    }

    $accessToken = $oauthClient->getAccessToken('authorization_code', [
      'code' => (string) $code,
    ]);

    if (!$accessToken instanceof AccessToken) {
      throw new InvalidCredentialsException();
    }

    return $this->claimsResolver->resolve($oauthClient, $accessToken, $provider);
  }
}
