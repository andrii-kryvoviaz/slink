<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class BootRunner {
  /**
   * @param iterable<BootStepInterface> $steps
   */
  public function __construct(
    #[AutowireIterator('slink.boot_step')]
    private iterable $steps,
  ) {}

  public function run(BootContext $context, BootReporterInterface $reporter): bool {
    $start = hrtime(true);
    $hasFailure = false;

    $ordered = iterator_to_array($this->steps, false);
    usort($ordered, static fn (BootStepInterface $a, BootStepInterface $b): int
      => $a->category()->order() <=> $b->category()->order());

    foreach ($ordered as $step) {
      $reporter->stepStarted($step);

      $stepStart = hrtime(true);

      try {
        $result = $step->run($context);
      } catch (\Throwable $e) {
        $result = BootResult::fail($e->getMessage(), $e->getTraceAsString());
      }

      $durationMs = (hrtime(true) - $stepStart) / 1_000_000;
      $reporter->stepCompleted($step, $result, $durationMs);

      if ($result->status->isFailure()) {
        $hasFailure = true;
        break;
      }
    }

    $totalMs = (hrtime(true) - $start) / 1_000_000;
    $reporter->footer($totalMs, $hasFailure);

    return !$hasFailure;
  }
}
