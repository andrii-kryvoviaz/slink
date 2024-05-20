<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SelfUserStatusChangeException extends SpecificationException {
  
  public function __construct() {
    parent::__construct('You cannot change your own status.');
  }
  
  function getProperty(): string {
    return 'id';
  }
}