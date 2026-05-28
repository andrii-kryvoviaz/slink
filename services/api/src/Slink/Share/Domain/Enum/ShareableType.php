<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Enum;

use Slink\Share\Domain\ValueObject\TargetPath;

enum ShareableType: string {
  case Image = 'image';
  case Collection = 'collection';

  public function urlPrefix(): string {
    return match ($this) {
      self::Image => 'i',
      self::Collection => 'c',
    };
  }

  public function targetPath(string $id, ?string $fileName = null): TargetPath {
    $path = match ($this) {
      self::Image => "/image/" . ($fileName ?? $id),
      self::Collection => "/collection/{$id}",
    };

    return TargetPath::fromString($path);
  }

  public function autoPublishOnCreate(): bool {
    return match ($this) {
      self::Image => false,
      self::Collection => true,
    };
  }
}
