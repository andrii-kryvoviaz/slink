<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

abstract class SpecificationException extends \LogicException {
  abstract function getProperty(): string;
  
  public function __construct(string $message) {
    parent::__construct($message);
  }

  /**
   * Return custom data for frontend component rendering
   * Override this method to provide additional data for custom error handling
   * @return array<string, mixed>
   */
  public function toPayload(): array {
    return [];
  }
}