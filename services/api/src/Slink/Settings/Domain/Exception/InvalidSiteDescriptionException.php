<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidSiteDescriptionException extends SpecificationException {
  public function __construct(string $message = 'Invalid site description') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.siteDescription';
  }
}
