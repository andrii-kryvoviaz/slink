<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Exception\OidcDiscoveryException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class OidcDiscovery implements OidcDiscoveryInterface {
  public function __construct(
    private HttpClientInterface $httpClient,
    private CacheInterface $cache,
  ) {}

  private static function normalizeDiscoveryUrl(string $url): string {
    $url = rtrim($url, '/');

    if (!str_ends_with($url, '/.well-known/openid-configuration')) {
      $url .= '/.well-known/openid-configuration';
    }

    return $url;
  }

  /**
   * @param string $discoveryUrl
   * @return array{authorizationEndpoint: string, tokenEndpoint: string, userinfoEndpoint: string, jwksUri: string}
   */
  #[\Override]
  public function discover(string $discoveryUrl): array {
    $discoveryUrl = self::normalizeDiscoveryUrl($discoveryUrl);
    $cacheKey = 'oidc_discovery_' . hash('sha256', $discoveryUrl);

    return $this->cache->get($cacheKey, function (ItemInterface $item) use ($discoveryUrl): array {
      $item->expiresAfter(86400);

      try {
        $response = $this->httpClient->request('GET', $discoveryUrl);
        $data = $response->toArray();
      } catch (\Throwable $e) {
        throw OidcDiscoveryException::httpError($discoveryUrl, $e);
      }

      $requiredKeys = ['authorization_endpoint', 'token_endpoint', 'userinfo_endpoint', 'jwks_uri'];
      $missingKeys = array_filter($requiredKeys, fn (string $key): bool => !isset($data[$key]));

      if (!empty($missingKeys)) {
        throw OidcDiscoveryException::missingKeys($discoveryUrl, $missingKeys);
      }

      return [
        'authorizationEndpoint' => $data['authorization_endpoint'],
        'tokenEndpoint' => $data['token_endpoint'],
        'userinfoEndpoint' => $data['userinfo_endpoint'],
        'jwksUri' => $data['jwks_uri'],
      ];
    });
  }
}
