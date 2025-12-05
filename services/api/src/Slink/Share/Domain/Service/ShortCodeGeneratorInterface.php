<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Service;

interface ShortCodeGeneratorInterface {
  public function generate(): string;
}
