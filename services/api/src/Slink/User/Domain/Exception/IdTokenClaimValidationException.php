<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class IdTokenClaimValidationException extends \RuntimeException {
  public function __construct(string $message, ?\Throwable $previous = null) {
    parent::__construct(
      message: sprintf('ID token claim validation failed: %s', $message),
      previous: $previous,
    );
  }

  public static function fromThrowable(\Throwable $previous): self {
    return new self($previous->getMessage(), $previous);
  }
}
