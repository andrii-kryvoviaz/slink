<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class ApiKeyNotFoundException extends InvalidDisplayNameException {
  public function __construct(string $message = 'API key not found') {
    parent::__construct($message);
  }
}
