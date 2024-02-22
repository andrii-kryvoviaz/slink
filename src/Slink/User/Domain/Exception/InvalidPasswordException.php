<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidPasswordException extends SpecificationException {
  public function __construct(string $message = 'Invalid password.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'password';
  }
}