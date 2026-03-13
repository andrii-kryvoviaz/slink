<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Slink\User\Domain\Contracts\OAuthProviderProfile;
use Slink\User\Domain\ValueObject\OAuth\RedirectUri;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class OAuthClientFactory implements OAuthClientFactoryInterface {
  public function __construct(
    private OidcDiscoveryInterface $oidcDiscovery,
    #[Autowire('%env(bool:OAUTH_VERIFY_SSL)%')]
    private bool $verifySsl = true,
  ) {}

  #[\Override]
  public function create(OAuthProviderProfile $provider, RedirectUri $redirectUri): GenericProvider {
    $discovery = $this->oidcDiscovery->discover($provider->getDiscoveryUrl());

    $genericProvider = new GenericProvider([
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

    if (!$this->verifySsl) {
      $genericProvider->setHttpClient(new Client(['verify' => false]));
    }

    return $genericProvider;
  }
}
