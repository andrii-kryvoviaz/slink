<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

class InvalidValueObjectException extends \DomainException {
  public function __construct(string $valueObject, string $reason) {
    parent::__construct(sprintf('Invalid %s: %s', $valueObject, $reason));
  }
}
