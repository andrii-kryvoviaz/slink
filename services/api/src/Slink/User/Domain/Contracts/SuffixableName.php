<?php

declare(strict_types=1);

namespace Slink\User\Domain\Contracts;

interface SuffixableName {
  public function withSuffix(int $suffix): static;
}
