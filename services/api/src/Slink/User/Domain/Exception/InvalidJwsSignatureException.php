<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class InvalidJwsSignatureException extends \RuntimeException {
  public function __construct(\Throwable $previous) {
    parent::__construct(
      message: sprintf('ID token signature validation failed: %s', $previous->getMessage()),
      previous: $previous,
    );
  }
}
