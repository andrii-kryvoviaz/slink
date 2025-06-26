<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Contract;

use DateTimeInterface;

interface CursorAwareInterface {
  public function getCursorId(): string;

  public function getCursorTimestamp(): DateTimeInterface;
}
