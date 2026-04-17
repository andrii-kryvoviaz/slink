<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\ValueObject\ShareResponse;
use Slink\Shared\Domain\ValueObject\ID;

interface OwnerShareInfoResolverInterface {
  public function resolve(
    string $shareableId,
    ShareableType $type,
    ?ID $ownerId,
    ?ID $viewerId,
  ): ?ShareResponse;
}
