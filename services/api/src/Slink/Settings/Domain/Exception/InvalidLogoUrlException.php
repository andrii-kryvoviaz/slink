<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidLogoUrlException extends SpecificationException {
  public function __construct(string $message = 'Invalid logo URL') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.logoUrl';
  }
}
