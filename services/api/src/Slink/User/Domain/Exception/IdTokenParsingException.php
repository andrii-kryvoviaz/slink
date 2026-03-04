<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class IdTokenParsingException extends \RuntimeException {
  public function __construct(\Throwable $previous) {
    parent::__construct(
      message: sprintf('Failed to parse ID token: %s', $previous->getMessage()),
      previous: $previous,
    );
  }
}
