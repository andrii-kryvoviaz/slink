<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Lock;

use RuntimeException;
use Throwable;

final class ResourceBusyException extends RuntimeException {
  public function __construct(string $message = 'Resource is busy', int $code = 0, ?Throwable $previous = null) {
    parent::__construct($message, $code, $previous);
  }
}
