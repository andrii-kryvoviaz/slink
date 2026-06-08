<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidChunkSizeException extends SpecificationException {
  public function __construct(string $message = 'Invalid chunk size') {
    parent::__construct($message);
  }

  function getProperty(): string {
    return 'image.chunkSize';
  }
}
