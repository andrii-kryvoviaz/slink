<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

class UsernameAlreadyExistException extends InvalidUsernameException {
  public function __construct() {
    parent::__construct('Username already exist.');
  }
}