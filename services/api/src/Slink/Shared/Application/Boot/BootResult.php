<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

final readonly class BootResult {
  /**
   * @param list<array{0: string, 1: string}> $settings
   */
  public function __construct(
    public BootStatus $status,
    public ?string $detail = null,
    public ?string $verboseOutput = null,
    public array $settings = [],
  ) {}

  public static function ok(?string $detail = null): self {
    return new self(BootStatus::Ok, $detail);
  }

  public static function upToDate(?string $detail = null): self {
    return new self(BootStatus::UpToDate, $detail);
  }

  public static function applied(string $detail): self {
    return new self(BootStatus::Applied, $detail);
  }

  public static function info(string $detail): self {
    return new self(BootStatus::Info, $detail);
  }

  /**
   * @param list<array{0: string, 1: string}> $settings
   */
  public static function settings(array $settings, ?string $summary = null): self {
    return new self(BootStatus::Info, $summary, null, $settings);
  }

  public static function skipped(?string $detail = null): self {
    return new self(BootStatus::Skipped, $detail);
  }

  public static function warn(string $detail, ?string $verboseOutput = null): self {
    return new self(BootStatus::Warn, $detail, $verboseOutput);
  }

  public static function fail(string $detail, ?string $verboseOutput = null): self {
    return new self(BootStatus::Fail, $detail, $verboseOutput);
  }
}
