<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

final class SelfBookmarkException extends SpecificationException {
  public function __construct() {
    parent::__construct('You cannot bookmark your own image');
  }

  public function getProperty(): string {
    return 'imageId';
  }
}
