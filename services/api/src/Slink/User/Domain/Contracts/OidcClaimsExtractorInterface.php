<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Slink\User\Domain\ValueObject\OAuth\OAuthClaims;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

interface OidcClaimsExtractorInterface {
  /**
   * @param array<string, mixed> $tokenValues
   * @param GenericProvider $oauthClient
   * @param AccessToken $accessToken
   * @param OAuthProviderView $provider
   * @return OAuthClaims
   */
  public function extract(array $tokenValues, GenericProvider $oauthClient, AccessToken $accessToken, OAuthProviderView $provider): OAuthClaims;
}
