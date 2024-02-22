<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidEmailException extends SpecificationException {
  public function __construct(string $message = 'Invalid email address.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'email';
  }
}