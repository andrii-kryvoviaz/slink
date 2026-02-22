<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class InvalidOAuthValueException extends \DomainException {
  public function __construct(string $valueObject, string $reason) {
    parent::__construct(sprintf('Invalid %s: %s', $valueObject, $reason));
  }
}
