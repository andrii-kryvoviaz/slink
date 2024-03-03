<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidOldPassword extends SpecificationException {
  
  public function __construct(string $message = 'Invalid old password provided.') {
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'old_password';
  }
}