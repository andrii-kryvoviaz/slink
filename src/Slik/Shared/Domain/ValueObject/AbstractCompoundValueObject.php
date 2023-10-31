<?php

declare(strict_types=1);

namespace Slik\Shared\Domain\ValueObject;

abstract readonly class AbstractCompoundValueObject {
  abstract public function toPayload(): array;
  
  abstract public static function fromPayload(array $payload): static;
}