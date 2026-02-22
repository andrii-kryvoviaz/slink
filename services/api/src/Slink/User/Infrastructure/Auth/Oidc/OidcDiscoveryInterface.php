<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

interface OidcDiscoveryInterface {
  /**
   * @param string $discoveryUrl
   * @return array{authorizationEndpoint: string, tokenEndpoint: string, userinfoEndpoint: string, jwksUri: string}
   */
  public function discover(string $discoveryUrl): array;
}
