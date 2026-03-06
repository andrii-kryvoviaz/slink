<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class UnsupportedJwtAlgorithmException extends \RuntimeException {
  public function __construct(string $algorithm) {
    parent::__construct(
      sprintf('Unsupported JWT signing algorithm: %s', $algorithm),
    );
  }
}
