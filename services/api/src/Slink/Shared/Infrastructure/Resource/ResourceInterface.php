<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Resource;

interface ResourceInterface {
  public function getType(): string;
}
