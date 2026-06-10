<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Exception;

final class UndeterminableImageExtensionException extends \RuntimeException {
  public function __construct() {
    parent::__construct('Unable to determine image file extension.');
  }
}
