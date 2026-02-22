<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class OAuthStateExpiredException extends \LogicException {
  public function __construct() {
    parent::__construct('Invalid or expired OAuth state');
  }
}
