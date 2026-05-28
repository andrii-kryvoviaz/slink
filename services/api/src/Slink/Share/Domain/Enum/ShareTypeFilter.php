<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareTypeFilter: string {
  case All = 'all';
  case Image = 'image';
  case Collection = 'collection';

  public function toShareableType(): ?ShareableType {
    return match ($this) {
      self::All => null,
      self::Image => ShareableType::Image,
      self::Collection => ShareableType::Collection,
    };
  }
}
