<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

class InvalidRefreshToken extends \LogicException {
  public function __construct() {
    parent::__construct('Invalid or expired refresh token.');
  }
}