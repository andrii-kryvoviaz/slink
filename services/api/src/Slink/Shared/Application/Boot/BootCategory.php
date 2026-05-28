<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

enum BootCategory: string {
  case Info = 'info';
  case Boot = 'boot';
  case Config = 'config';

  public function order(): int {
    return match ($this) {
      self::Info => 0,
      self::Boot => 1,
      self::Config => 2,
    };
  }
}
