<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

class EmailAlreadyExistException extends InvalidEmailException {
  public function __construct() {
    parent::__construct('Email already registered.');
  }
}