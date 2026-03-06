<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

final class MissingKeyIdException extends \RuntimeException {
  public function __construct() {
    parent::__construct('ID token is missing the "kid" header claim');
  }
}
