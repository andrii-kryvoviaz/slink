<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidCredentialsException extends SpecificationException {
  /**
   * @param mixed ...$args
   */
  public function __construct(mixed ...$args) {
    if (empty($args)) {
      $args = ['Invalid credentials. Please check your username/email and password and try again.'];
    }
    
    parent::__construct(...$args);
  }
  
  /**
   * @return string
   */
  #[\Override]
  function getProperty(): string {
    return 'credentials';
  }
}