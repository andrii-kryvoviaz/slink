<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Persistence\EventStore\AbstractStoreRepository;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;

final class OAuthProviderStore extends AbstractStoreRepository implements OAuthProviderStoreRepositoryInterface {
  static function getAggregateRootClass(): string {
    return OAuthProvider::class;
  }

  #[\Override]
  public function get(ID $id): OAuthProvider {
    $provider = $this->retrieve($id);
    if (!$provider instanceof OAuthProvider) {
      throw new \RuntimeException('Expected instance of OAuthProvider, got ' . get_class($provider));
    }
    return $provider;
  }

  #[\Override]
  public function store(OAuthProvider $provider): void {
    $this->persist($provider);
  }
}
