<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\OAuthContext;
use Slink\User\Domain\ValueObject\OAuth\OAuthState;

interface OAuthStateManagerInterface {
  public function storeState(OAuthState $state, OAuthContext $context): void;

  public function consume(OAuthState $state): OAuthContext;
}
