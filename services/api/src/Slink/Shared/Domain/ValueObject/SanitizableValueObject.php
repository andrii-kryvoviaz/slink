<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\ValueObject;

interface SanitizableValueObject {
  public function sanitize(): static;
}
