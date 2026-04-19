<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

use Slink\Shared\Domain\ValueObject\ID;

interface ShareUnlockVerifierInterface {
  public function isVerified(ID $shareId): bool;
}
