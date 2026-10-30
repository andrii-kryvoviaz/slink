<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class SsoProviderRequiredException extends SpecificationException {
  public function __construct(string $message = 'An enabled SSO provider is required to turn off local authentication') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'localAuthEnabled';
  }
}
