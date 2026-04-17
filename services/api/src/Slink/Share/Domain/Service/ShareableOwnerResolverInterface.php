<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Share\Domain\ValueObject\ShareableReference;

interface ShareableOwnerResolverInterface {
  public function resolveOwnerId(ShareableReference $shareable): ?string;
}
