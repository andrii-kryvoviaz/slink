<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SelfUserRoleChangeException extends SpecificationException {
  
  public function __construct() {
    parent::__construct('You cannot grant or revoke roles from yourself. Use CLI instead.');
  }
  
  function getProperty(): string {
    return 'id';
  }
}
