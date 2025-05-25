<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidDisplayNameException extends SpecificationException {
  public function __construct(string $message = 'Invalid display name') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'display_name';
  }
}