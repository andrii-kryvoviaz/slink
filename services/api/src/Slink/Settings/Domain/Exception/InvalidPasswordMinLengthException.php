<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidPasswordMinLengthException extends SpecificationException {
  public function __construct(string $message = 'Invalid password min length') {
    parent::__construct($message);
  }
  
  function getProperty(): string {
    return 'password.minLength';
  }
}