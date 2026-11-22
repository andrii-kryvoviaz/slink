<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidHiddenThemesException extends SpecificationException {
  public function __construct(string $message = 'Invalid hidden themes') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'customization.hiddenThemes';
  }
}
