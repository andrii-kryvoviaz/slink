<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class JwksKeyNotFoundException extends \RuntimeException {
  public function __construct(string $kid) {
    parent::__construct(sprintf('Key with kid "%s" not found in JWKS', $kid));
  }
}
