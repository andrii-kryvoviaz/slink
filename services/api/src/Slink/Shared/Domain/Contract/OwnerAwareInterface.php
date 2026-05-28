<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Contract;

use Slink\Shared\Domain\ValueObject\ID;

interface OwnerAwareInterface {
  public function isOwnedBy(?ID $userId): bool;
}
