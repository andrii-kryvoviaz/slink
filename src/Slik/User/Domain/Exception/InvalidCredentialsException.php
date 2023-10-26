<?php

declare(strict_types=1);

namespace Slik\User\Domain\Exception;

class InvalidCredentialsException extends \RuntimeException {
  public function __construct(...$args) {
    if (empty($args)) {
      $args = ['Invalid credentials'];
    }
    
    parent::__construct(...$args);
  }
}