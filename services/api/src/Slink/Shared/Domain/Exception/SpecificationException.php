<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

abstract class SpecificationException extends \LogicException {
  abstract function getProperty(): string;
  
  public function __construct(string $message) {
    parent::__construct($message);
  }
}