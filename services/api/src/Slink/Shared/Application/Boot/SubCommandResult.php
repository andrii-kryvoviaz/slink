<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

final readonly class SubCommandResult {
  public function __construct(
    public int $exitCode,
    public string $output,
  ) {}

  public function isSuccessful(): bool {
    return $this->exitCode === 0;
  }

  public function contains(string $needle): bool {
    return str_contains($this->output, $needle);
  }
}
