<?php

declare(strict_types=1);

namespace Slink\Shared\Domain\Enum;

trait ValidatorAwareEnumTrait {
  /**
   * @return array<string>
   */
  public static function values(): array {
    return array_map(static fn (\BackedEnum $item): string => $item->value, self::cases());
  }
}