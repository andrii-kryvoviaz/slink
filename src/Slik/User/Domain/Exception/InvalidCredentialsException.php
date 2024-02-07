<?php

declare(strict_types=1);

namespace Slik\User\Domain\Exception;

class InvalidCredentialsException extends \RuntimeException {
  /**
   * @param mixed ...$args
   */
  public function __construct(mixed ...$args) {
    if (empty($args)) {
      $args = ['Invalid credentials'];
    }
    
    parent::__construct(...$args);
  }
}