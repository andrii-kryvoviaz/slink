<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Enum\OAuthProvider;
use Slink\User\Domain\Exception\DuplicateOAuthProviderException;

interface UniqueOAuthProviderSpecificationInterface {
  /**
   * @throws DuplicateOAuthProviderException
   */
  public function ensureUnique(OAuthProvider $provider): void;
}
