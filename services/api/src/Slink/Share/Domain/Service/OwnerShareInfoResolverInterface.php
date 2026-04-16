<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\Enum\ShareableType;
use Slink\Shared\Domain\ValueObject\ID;

interface OwnerShareInfoResolverInterface {
  /**
   * @return array{shareInfo?: array{shareId: string, shareUrl: string, type: string}}
   */
  public function resolve(string $shareableId, ShareableType $type, ID $ownerId, ?ID $viewerId): array;
}
