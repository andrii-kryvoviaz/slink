<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidUsernameException extends SpecificationException {
  public function __construct(string $message = 'Invalid username') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'username';
  }
}