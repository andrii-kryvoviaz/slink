<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidThemeException extends SpecificationException {
  public function __construct(string $message = 'Invalid theme') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.themes';
  }
}
