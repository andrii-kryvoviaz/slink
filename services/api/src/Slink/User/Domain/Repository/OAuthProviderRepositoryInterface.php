<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

interface OAuthProviderRepositoryInterface {
  public function findByProvider(OAuthProvider $provider): ?OAuthProviderView;

  /**
   * @return array<int, OAuthProviderView>
   */
  public function getProviders(OAuthProviderFilter $filter): array;

  public function findById(ID $id): ?OAuthProviderView;

  public function save(OAuthProviderView $provider): void;

  public function delete(OAuthProviderView $provider): void;
}
