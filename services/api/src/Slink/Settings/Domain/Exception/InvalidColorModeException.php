<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidColorModeException extends SpecificationException {
  public function __construct(string $message = 'Invalid color mode') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.colorMode';
  }
}
