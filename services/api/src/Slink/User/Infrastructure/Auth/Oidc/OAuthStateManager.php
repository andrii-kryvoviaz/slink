<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth\Oidc;

use Slink\User\Domain\Contracts\OAuthStateManagerInterface;
use Slink\User\Domain\Exception\OAuthStateExpiredException;
use Slink\User\Domain\ValueObject\OAuth\OAuthContext;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class OAuthStateManager implements OAuthStateManagerInterface {
  public function __construct(
    private CacheInterface $cache,
  ) {}

  #[\Override]
  public function storeState(OAuthState $state, OAuthContext $context): void {
    $cacheKey = 'oauth_state_' . hash('sha256', $state->toString());

    $this->cache->get($cacheKey, function (ItemInterface $item) use ($context): array {
      $item->expiresAfter(300);

      return $context->toPayload();
    });
  }

  #[\Override]
  public function consume(OAuthState $state): OAuthContext {
    $cacheKey = 'oauth_state_' . hash('sha256', $state->toString());

    /** @var array{provider: string, redirectUri: string, pkceVerifier: string|null}|null $data */
    $data = $this->cache->get($cacheKey, fn (): null => null);

    if ($data === null) {
      throw new OAuthStateExpiredException();
    }

    $this->cache->delete($cacheKey);

    return OAuthContext::fromPayload($data);
  }
}
