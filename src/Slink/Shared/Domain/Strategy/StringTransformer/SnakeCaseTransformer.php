<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy\StringTransformer;

final class SnakeCaseTransformer implements StringTransformer {
  
  #[\Override]
  public function transform(string $value): string {
    return \strtolower(\preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
  }
}