<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy\StringTransformer;

final class CamelCaseTransformer implements StringTransformer {
  
  #[\Override]
  public function transform(string $value): string {
    return \lcfirst(\str_replace(' ', '', \ucwords(\str_replace(['_', '-'], ' ', $value))));
  }
}