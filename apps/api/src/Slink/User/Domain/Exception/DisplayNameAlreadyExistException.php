<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

class DisplayNameAlreadyExistException extends InvalidDisplayNameException {
  public function __construct() {
      parent::__construct('Display name already exist.');
  }
}