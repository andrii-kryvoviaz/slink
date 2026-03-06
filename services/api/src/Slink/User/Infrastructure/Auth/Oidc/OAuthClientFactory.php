<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use League\OAuth2\Client\Provider\GenericProvider;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;

final readonly class OAuthClientFactory implements OAuthClientFactoryInterface {
  public function __construct(
    private OidcDiscoveryInterface $oidcDiscovery,
  ) {}

  #[\Override]
  public function create(OAuthProviderProfile $provider, RedirectUri $redirectUri): GenericProvider {
    $discovery = $this->oidcDiscovery->discover($provider->getDiscoveryUrl());

    return new GenericProvider([
      'clientId' => (string) $provider->getClientId(),
      'clientSecret' => (string) $provider->getClientSecret(),
      'redirectUri' => (string) $redirectUri,
      'urlAuthorize' => $discovery->getAuthorizationEndpoint(),
      'urlAccessToken' => $discovery->getTokenEndpoint(),
      'urlResourceOwnerDetails' => $discovery->getUserinfoEndpoint(),
      'pkceMethod' => GenericProvider::PKCE_METHOD_S256,
      'scopes' => (string) $provider->getScopes(),
      'scopeSeparator' => ' ',
    ]);
  }
}
