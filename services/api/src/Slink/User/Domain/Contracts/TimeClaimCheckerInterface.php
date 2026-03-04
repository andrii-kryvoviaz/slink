<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\TokenClaims;

interface TimeClaimCheckerInterface {
  public function check(TokenClaims $claims): void;
}
