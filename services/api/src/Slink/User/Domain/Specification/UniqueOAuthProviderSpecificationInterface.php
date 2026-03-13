<?php

declare(strict_types=1);

namespace Slink\User\Domain\Specification;

use Slink\User\Domain\Exception\DuplicateOAuthProviderException;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

interface UniqueOAuthProviderSpecificationInterface {
  /**
   * @throws DuplicateOAuthProviderException
   */
  public function ensureUnique(ProviderSlug $slug): void;
}
