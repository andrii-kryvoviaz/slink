<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;
use Slink\User\Domain\ValueObject\Role;

class InvalidUserRole extends SpecificationException {
  public function __construct(?Role $role = null) {
    $message = $role ? sprintf('Invalid user role: %s', $role->getRole()) : 'Invalid user role';
    
    parent::__construct($message);
  }
  
  #[\Override]
  function getProperty(): string {
    return 'role';
  }
}