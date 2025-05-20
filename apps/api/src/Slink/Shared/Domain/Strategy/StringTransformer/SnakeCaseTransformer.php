<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy\StringTransformer;

final class SnakeCaseTransformer implements StringTransformer {
  
  #[\Override]
  public function transform(string $value): string {
    $replaced = \preg_replace('/(?<!^)[A-Z]/', '_$0', $value);
    
    if ($replaced === null) {
      throw new \RuntimeException('Error while transforming string to snake case');
    }
    
    return \strtolower($replaced);
  }
}