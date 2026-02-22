<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\OAuthProvider;

interface OAuthProviderStoreRepositoryInterface {
  public function get(ID $id): OAuthProvider;
  public function store(OAuthProvider $provider): void;
}
