<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class InvalidShareExpirationException extends SpecificationException {
  public function __construct() {
    parent::__construct('Expiration must be in the future');
  }

  public function getProperty(): string {
    return 'expiresAt';
  }
}
