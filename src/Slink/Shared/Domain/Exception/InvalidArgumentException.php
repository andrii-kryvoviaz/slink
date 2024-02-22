<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

class InvalidArgumentException extends SpecificationException {
  private readonly string $property;
  
  public function __construct(string $message, string $property = 'error') {
    parent::__construct($message);
    $this->property = $property;
  }
  
  #[\Override]
  function getProperty(): string {
    return $this->property;
  }
}