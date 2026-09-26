<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class DemoSeedReport {
  /** @var list<string> */
  private array $created = [];

  /** @var list<string> */
  private array $skipped = [];

  /** @var list<string> */
  private array $failures = [];

  public function __construct(
    private readonly string $area,
  ) {
  }

  public function created(string $item): void {
    $this->created[] = $item;
  }

  public function skipped(string $item): void {
    $this->skipped[] = $item;
  }

  /**
   * @template T
   * @param \Closure(): T $action
   * @return T|null
   */
  public function attempt(string $item, \Closure $action): mixed {
    try {
      return $action();
    } catch (\Throwable $e) {
      $this->failures[] = sprintf('%s: %s', $item, $e->getMessage());

      return null;
    }
  }

  public function hasFailures(): bool {
    return $this->failures !== [];
  }

  public function render(SymfonyStyle $io): void {
    $io->section(sprintf(
      '%s: %d created, %d skipped, %d failed',
      $this->area,
      count($this->created),
      count($this->skipped),
      count($this->failures),
    ));

    if ($this->created !== []) {
      $io->listing($this->created);
    }

    if ($this->skipped !== []) {
      $io->text('Already present: ' . implode(', ', $this->skipped));
    }

    foreach ($this->failures as $failure) {
      $io->writeln(sprintf('<error>%s</error>', OutputFormatter::escape($failure)));
    }
  }
}
