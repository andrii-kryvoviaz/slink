<?php

declare(strict_types=1);

namespace Slink\User\Domain\Service;

use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Username;

interface UsernameGeneratorInterface {
  public function generate(DisplayName $displayName): ?Username;
}
