<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class PrivateImageBookmarkException extends SpecificationException {
  public function __construct() {
    parent::__construct('Cannot bookmark a private image');
  }

  public function getProperty(): string {
    return 'imageId';
  }
}
