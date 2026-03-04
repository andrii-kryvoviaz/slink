<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

use Slink\User\Domain\ValueObject\OAuth\JwtHeader;

interface AlgorithmHeaderCheckerInterface {
  public function check(JwtHeader $header): void;
}
