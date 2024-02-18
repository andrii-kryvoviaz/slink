<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class EmailAlreadyExistException extends SpecificationException {
  public function __construct() {
    parent::__construct('Email already registered.');
  }
  
  #[\Override]
  function getProperty(): string {
    return 'email';
  }
}