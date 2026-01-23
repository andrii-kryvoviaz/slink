<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

enum ShareableType: string {
  case Image = 'image';
  case Collection = 'collection';

  public function urlPrefix(): string {
    return match ($this) {
      self::Image => 'i',
      self::Collection => 'c',
    };
  }

  public function targetPath(string $id, ?string $fileName = null): string {
    return match ($this) {
      self::Image => "/image/" . ($fileName ?? $id),
      self::Collection => "/collection/{$id}",
    };
  }
}
