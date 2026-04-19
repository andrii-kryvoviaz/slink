<?php

declare(strict_types=1);

namespace Slink\Share\Domain\AccessRule;

use Slink\Shared\Domain\ValueObject\Date\DateTime;

interface ExpirationAware {
  public function getExpiresAt(): ?DateTime;
}
