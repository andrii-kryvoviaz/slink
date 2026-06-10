<?php

declare(strict_types=1);

namespace Slink\User\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum ExifMetadataPreference: string {
  use ValidatorAwareEnumTrait;

  case Default = 'default';
  case Strip = 'strip';
  case Keep = 'keep';

  public function resolveStripExif(bool $serverDefault): bool {
    return match ($this) {
      self::Strip => true,
      self::Keep => false,
      self::Default => $serverDefault,
    };
  }
}
