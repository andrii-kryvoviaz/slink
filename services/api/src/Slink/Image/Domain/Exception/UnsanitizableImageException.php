<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class UnsanitizableImageException extends SpecificationException {
  public function __construct() {
    parent::__construct('This SVG could not be processed. It may be malformed or nested too deeply.');
  }

  public function getProperty(): string {
    return 'image';
  }
}
