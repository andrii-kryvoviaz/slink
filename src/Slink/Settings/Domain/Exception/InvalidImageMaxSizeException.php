<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Exception;

use Slink\Shared\Domain\Exception\SpecificationException;

class InvalidImageMaxSizeException extends SpecificationException {
  public function __construct(string $message = 'Invalid image max size') {
    parent::__construct($message);
  }
  
  function getProperty(): string {
    return 'image.maxSize';
  }
}