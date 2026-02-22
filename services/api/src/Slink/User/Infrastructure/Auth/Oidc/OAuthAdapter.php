<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Contracts\OAuthAdapterInterface;
use Slink\User\Domain\Contracts\OAuthClientFactoryInterface;
use Slink\User\Domain\Contracts\OAuthStateManagerInterface;
use Slink\User\Domain\Contracts\OidcClaimsExtractorInterface;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\InvalidCredentialsException;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Slink\User\Domain\ValueObject\OAuth\AuthorizationCode;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;
use Slink\User\Domain\ValueObject\OAuth\OAuthContext;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Slink\User\Domain\ValueObject\OAuth\PkceVerifier;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final readonly class OAuthAdapter implements OAuthAdapterInterface {
  public function __construct(
    private OAuthClientFactoryInterface $clientFactory,
    private OAuthStateManagerInterface $stateManager,
    private OidcClaimsExtractorInterface $claimsExtractor,
    private OAuthProviderRepositoryInterface $providerRepository,
  ) {}

  #[\Override]
  public function getAuthorizationUrl(OAuthProviderView $provider, RedirectUri $redirectUri): string {
    $oauthClient = $this->clientFactory->create($provider, $redirectUri);

    $authorizationUrl = $oauthClient->getAuthorizationUrl([
      'scope' => $provider->getScopes(),
    ]);

    $context = OAuthContext::create(
      OAuthProvider::from($provider->getSlug()),
      $redirectUri,
      PkceVerifier::fromNullableString($oauthClient->getPkceCode()),
    );

    $this->stateManager->storeState(
      OAuthState::fromString($oauthClient->getState()),
      $context,
    );

    return $authorizationUrl;
  }

  #[\Override]
  public function exchangeCode(AuthorizationCode $code, OAuthState $state): OAuthClaims {
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
      'code' => $code->toString(),
    ]);

    return $this->claimsExtractor->extract(
      $accessToken->getValues(),
      $oauthClient,
      $accessToken,
      $provider,
    );
  }
}
