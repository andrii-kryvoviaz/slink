<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Strategy\StringTransformer;

interface StringTransformer {
  public function transform(string $value): string;
}