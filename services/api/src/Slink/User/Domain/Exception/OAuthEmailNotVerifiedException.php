<?php

declare(strict_types=1);

namespace Slink\User\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class OAuthEmailNotVerifiedException extends SpecificationException {
  public function __construct() {
    parent::__construct('Email must be verified by the SSO provider before signing in.');
  }

  #[\Override]
  function getProperty(): string {
    return 'email';
  }
}
