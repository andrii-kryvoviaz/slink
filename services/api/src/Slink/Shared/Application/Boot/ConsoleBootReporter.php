<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleBootReporter implements BootReporterInterface {
  private const HEADER_WIDTH = 64;
  private const LABEL_WIDTH = 30;
  private const PREFIX_WIDTH = 9;
  private const VERBOSE_LINE_LIMIT = 5;
  private const SETTING_INDENT_PREFIX = '            ';
  private const SETTING_GAP = 4;

  private ?BootCategory $currentCategory = null;
  private bool $printedAnyCategory = false;

  /**
   * @var list<array{step: BootStepInterface, result: BootResult, durationMs: float}>
   */
  private array $bufferedSteps = [];

  public function __construct(
    private readonly OutputInterface $output,
  ) {}

  /**
   * @param array<string, string> $meta
   */
  public function header(string $version, array $meta): void {
    $segments = [$version, ...array_values($meta)];
    $metaText = implode('  •  ', $segments);

    $left = sprintf('━━━ <options=bold>Slink</> ━━━ %s ', $metaText);
    $padding = max(3, self::HEADER_WIDTH - Helper::width($left));
    $rule = str_repeat('━', $padding);

    $this->output->writeln('');
    $this->output->writeln(sprintf('<fg=gray>%s</>', $left . $rule));
    $this->output->writeln('');
  }

  public function stepStarted(BootStepInterface $step): void {
    // Render on completion only.
  }

  public function stepCompleted(BootStepInterface $step, BootResult $result, float $durationMs): void {
    $category = $step->category();

    if ($category !== $this->currentCategory) {
      $this->flushBuffered();

      if ($this->printedAnyCategory) {
        $this->output->writeln('');
      }

      $this->currentCategory = $category;
      $this->printedAnyCategory = true;
    }

    if ($category === BootCategory::Boot) {
      $this->renderActionStep($step, $result, $durationMs);
      return;
    }

    $this->bufferedSteps[] = compact('step', 'result', 'durationMs');
  }

  public function footer(float $totalMs, bool $hasFailure): void {
    $this->flushBuffered();
    $this->output->writeln('');

    if ($hasFailure) {
      $this->output->writeln(sprintf('<fg=red;options=bold>boot failed</> after %.0fms', $totalMs));
      return;
    }

    $this->output->writeln(sprintf('<fg=green;options=bold>ready</> in %.2fs', $totalMs / 1000));
    $this->output->writeln('');
  }

  private function flushBuffered(): void {
    if ($this->bufferedSteps === []) {
      return;
    }

    $keyWidth = 0;
    foreach ($this->bufferedSteps as $entry) {
      foreach ($entry['result']->settings as [$key, $_value]) {
        $keyWidth = max($keyWidth, mb_strlen($key));
      }
    }
    $keyWidth += self::SETTING_GAP;

    foreach ($this->bufferedSteps as $entry) {
      $this->renderSettingsStep($entry['step'], $entry['result'], $keyWidth);
    }

    $this->bufferedSteps = [];
  }

  private function renderActionStep(BootStepInterface $step, BootResult $result, float $durationMs): void {
    $prefix = $this->prefix($step->category());
    $padded = $this->paddedLabel($step->label());

    $text = $result->detail ?? $result->status->value;
    $colored = $this->colorize($text, $result->status);

    $timing = '';
    if ($durationMs >= 1) {
      $timing = sprintf('  <fg=gray>%dms</>', (int) round($durationMs));
    }

    $this->output->writeln(sprintf('%s%s %s%s', $prefix, $padded, $colored, $timing));

    $this->writeVerboseOutput($result);
  }

  private function renderSettingsStep(BootStepInterface $step, BootResult $result, int $keyWidth): void {
    $prefix = $this->prefix($step->category());

    $this->output->writeln(sprintf('%s%s:', $prefix, $step->label()));

    foreach ($result->settings as [$key, $value]) {
      $paddedKey = str_pad($key, $keyWidth, ' ', STR_PAD_RIGHT);
      $coloredValue = $this->colorizeSettingValue($value);

      $this->output->writeln(sprintf(
        '%s<fg=gray>%s</>%s',
        self::SETTING_INDENT_PREFIX,
        $paddedKey,
        $coloredValue,
      ));
    }

    $this->writeVerboseOutput($result);
  }

  private function writeVerboseOutput(BootResult $result): void {
    if ($result->verboseOutput === null || $result->verboseOutput === '') {
      return;
    }

    $lines = explode("\n", trim($result->verboseOutput));
    $shown = array_slice($lines, 0, self::VERBOSE_LINE_LIMIT);

    foreach ($shown as $line) {
      $this->output->writeln('       <fg=gray>│</> ' . $line);
    }

    $remaining = count($lines) - count($shown);
    if ($remaining > 0) {
      $this->output->writeln(sprintf('       <fg=gray>│ … %d more line(s) suppressed</>', $remaining));
    }
  }

  private function prefix(BootCategory $category): string {
    $tag = sprintf('[%s]', $category->value);
    $padded = str_pad($tag, self::PREFIX_WIDTH - 1, ' ', STR_PAD_RIGHT);

    return sprintf('<fg=blue>%s</> ', $padded);
  }

  private function paddedLabel(string $label): string {
    return str_pad($label . ' ', self::LABEL_WIDTH, '.', STR_PAD_RIGHT);
  }

  private function colorize(string $text, BootStatus $status): string {
    $color = match ($status) {
      BootStatus::Ok, BootStatus::Applied, BootStatus::UpToDate => 'green',
      BootStatus::Skipped => 'gray',
      BootStatus::Warn => 'yellow',
      BootStatus::Fail => 'red;options=bold',
      BootStatus::Info => null,
    };

    if ($color === null) {
      return $text;
    }

    return sprintf('<fg=%s>%s</>', $color, $text);
  }

  private function colorizeSettingValue(string $value): string {
    return match (strtolower($value)) {
      'enabled' => sprintf('<fg=green>%s</>', $value),
      'disabled', 'not required' => sprintf('<fg=gray>%s</>', $value),
      'required' => sprintf('<fg=yellow>%s</>', $value),
      default => $value,
    };
  }
}
