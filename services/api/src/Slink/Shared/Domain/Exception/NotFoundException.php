<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

class NotFoundException extends \Exception {
  public function __construct(string $message = 'Resource not found') {
    parent::__construct($message);
  }
}
