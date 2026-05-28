<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Boot;

enum BootStatus: string {
  case Ok = 'ok';
  case UpToDate = 'up-to-date';
  case Applied = 'applied';
  case Info = 'info';
  case Warn = 'warn';
  case Fail = 'fail';
  case Skipped = 'skipped';

  public function isFailure(): bool {
    return $this === self::Fail;
  }
}
