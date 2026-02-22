<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use League\OAuth2\Client\Provider\GenericProvider;
use Slink\User\Domain\Contracts\OAuthClientFactoryInterface;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

final readonly class OAuthClientFactory implements OAuthClientFactoryInterface {
  public function __construct(
    private OidcDiscoveryInterface $oidcDiscovery,
  ) {}

  #[\Override]
  public function create(OAuthProviderView $provider, RedirectUri $redirectUri): GenericProvider {
    $endpoints = $this->oidcDiscovery->discover($provider->getDiscoveryUrl());

    return new GenericProvider([
      'clientId' => $provider->getClientId(),
      'clientSecret' => $provider->getClientSecret(),
      'redirectUri' => $redirectUri->toString(),
      'urlAuthorize' => $endpoints['authorizationEndpoint'],
      'urlAccessToken' => $endpoints['tokenEndpoint'],
      'urlResourceOwnerDetails' => $endpoints['userinfoEndpoint'],
      'pkceMethod' => GenericProvider::PKCE_METHOD_S256,
      'scopes' => $provider->getScopes(),
      'scopeSeparator' => ' ',
    ]);
  }
}
