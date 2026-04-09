<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Exception;

class InvalidArgumentException extends SpecificationException {
  private readonly string $property;
  /**
   * @var array<string, scalar|null>
   */
  private readonly array $params;
  
  /**
   * @param array<string, scalar|null> $params
   */
  public function __construct(string $message, string $property = 'error', array $params = []) {
    parent::__construct($message);
    $this->property = $property;
    $this->params = $params;
  }
  
  #[\Override]
  function getProperty(): string {
    return $this->property;
  }

  #[\Override]
  public function toPayload(): array {
    if (empty($this->params)) {
      return [];
    }

    return ['params' => $this->params];
  }
}
