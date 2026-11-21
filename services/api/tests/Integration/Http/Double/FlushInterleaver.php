<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Double;

final class FlushInterleaver {
  public function __construct(private ?\Closure $write) {
  }

  public function preFlush(): void {
    if ($this->write === null) {
      return;
    }

    $write = $this->write;
    $this->write = null;
    $write();
  }

  public function hasRun(): bool {
    return $this->write === null;
  }
}
