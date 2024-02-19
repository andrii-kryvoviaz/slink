<?php

declare(strict_types=1);

namespace Slink\User\Domain\Repository;

use Ramsey\Uuid\UuidInterface;
use Slink\User\Domain\ValueObject\DisplayName;

interface CheckUserByDisplayNameInterface {
  /**
   * @param DisplayName $displayName
   * @return ?UuidInterface
   */
  public function existsDisplayName(DisplayName $displayName): ?UuidInterface;
}