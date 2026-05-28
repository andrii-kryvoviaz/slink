<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

interface BootReporterInterface {
  /**
   * @param array<string, string> $meta
   */
  public function header(string $version, array $meta): void;

  public function stepStarted(BootStepInterface $step): void;

  public function stepCompleted(BootStepInterface $step, BootResult $result, float $durationMs): void;

  public function footer(float $totalMs, bool $hasFailure): void;
}
