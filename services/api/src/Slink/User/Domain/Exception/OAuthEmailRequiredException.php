<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class OAuthEmailRequiredException extends SpecificationException {
  public function __construct() {
    parent::__construct('An email address is required for SSO authentication.');
  }

  #[\Override]
  function getProperty(): string {
    return 'email';
  }
}
