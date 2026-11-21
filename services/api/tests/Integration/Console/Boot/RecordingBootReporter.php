<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use Slink\Shared\Application\Boot\BootReporterInterface;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStatus;
use Slink\Shared\Application\Boot\BootStepInterface;

final class RecordingBootReporter implements BootReporterInterface {
  /** @var array<class-string<BootStepInterface>, BootResult> */
  private array $results = [];

  /**
   * @param array<string, string> $meta
   */
  public function header(string $version, array $meta): void {
  }

  public function stepStarted(BootStepInterface $step): void {
  }

  public function stepCompleted(BootStepInterface $step, BootResult $result, float $durationMs): void {
    $this->results[$step::class] = $result;
  }

  public function footer(float $totalMs, bool $hasFailure): void {
  }

  /**
   * @param class-string<BootStepInterface> $step
   */
  public function resultOf(string $step): ?BootResult {
    return $this->results[$step] ?? null;
  }

  /**
   * @return list<string>
   */
  public function details(BootStatus $status): array {
    $details = [];

    foreach ($this->results as $result) {
      if ($result->status === $status) {
        $details[] = (string) $result->detail;
      }
    }

    return $details;
  }
}
