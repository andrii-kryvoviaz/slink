<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Resource;

interface ResourceInterface {
  public function getType(): string;
}
