<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy\StringTransformer;

final class PassthroughTransformer implements StringTransformer {
  
  #[\Override]
  public function transform(string $value): string {
    return $value;
  }
}
