<?php

declare(strict_types=1);

namespace Slik\User\Domain\Exception;

class EmailAlreadyExistException extends \LogicException {
  public function __construct() {
    parent::__construct('Email already registered.');
  }
}