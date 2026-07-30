<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SelfUserPurgeException extends SpecificationException {

  public function __construct() {
    parent::__construct('You cannot purge your own account.');
  }

  function getProperty(): string {
    return 'id';
  }
}
