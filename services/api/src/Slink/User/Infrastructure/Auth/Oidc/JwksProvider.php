<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Jose\Component\Core\JWKSet;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class JwksProvider {
  public function __construct(
    private HttpClientInterface $httpClient,
    private CacheInterface $cache,
  ) {}

  public function getKeySet(string $jwksUri, string $kid): JWKSet {
    $jwkSet = $this->fetchKeys($jwksUri);

    if (!$jwkSet->has($kid)) {
      $jwkSet = $this->fetchKeys($jwksUri, forceRefresh: true);
    }

    if (!$jwkSet->has($kid)) {
      throw new \RuntimeException(sprintf('Key with kid "%s" not found in JWKS', $kid));
    }

    return $jwkSet;
  }

  private function fetchKeys(string $jwksUri, bool $forceRefresh = false): JWKSet {
    $cacheKey = 'jwks_' . hash('sha256', $jwksUri);

    if ($forceRefresh) {
      $this->cache->delete($cacheKey);
    }

    return $this->cache->get($cacheKey, function (ItemInterface $item) use ($jwksUri): JWKSet {
      $item->expiresAfter(86400);

      $response = $this->httpClient->request('GET', $jwksUri);

      return JWKSet::createFromKeyData($response->toArray());
    });
  }

}
