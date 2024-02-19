<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class DisplayNameAlreadyExistException extends SpecificationException {
  public function __construct() {
      parent::__construct('Display name already exist.');
  }
  
  #[\Override]
  function getProperty(): string {
    return 'display_name';
  }
}