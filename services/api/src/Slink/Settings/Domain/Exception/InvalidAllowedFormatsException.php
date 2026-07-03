<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidAllowedFormatsException extends SpecificationException {
  public function __construct(string $message = 'Invalid allowed formats') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'image.allowedFormats';
  }
}
