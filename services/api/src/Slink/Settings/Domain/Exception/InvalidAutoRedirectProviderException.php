<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidAutoRedirectProviderException extends SpecificationException {
  public function __construct(string $message = 'Invalid auto-redirect provider') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'user.autoRedirectProviderId';
  }
}
