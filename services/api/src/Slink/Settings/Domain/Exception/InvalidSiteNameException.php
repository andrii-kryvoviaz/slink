<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidSiteNameException extends SpecificationException {
  public function __construct(string $message = 'Invalid site name') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.siteName';
  }
}
