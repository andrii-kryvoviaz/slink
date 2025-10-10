<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageFormat: string {
  case JPEG = 'jpg';
  case PNG = 'png';
  case GIF = 'gif';
  case WEBP = 'webp';
  case AVIF = 'avif';

  public static function fromString(string $format): self {
    return match (strtolower($format)) {
      'jpg', 'jpeg' => self::JPEG,
      'png' => self::PNG,
      'gif' => self::GIF,
      'webp' => self::WEBP,
      'avif', 'heif' => self::AVIF,
      default => self::JPEG
    };
  }

  public static function fromMimeType(string $mimeType): ?self {
    return match ($mimeType) {
      'image/jpeg', 'image/jpg' => self::JPEG,
      'image/png' => self::PNG,
      'image/gif' => self::GIF,
      'image/webp' => self::WEBP,
      'image/avif' => self::AVIF,
      default => null
    };
  }

  public function getExtension(): string {
    return $this->value;
  }

  public function supportsAnimation(): bool {
    return match ($this) {
      self::GIF, self::WEBP, self::AVIF => true,
      default => false
    };
  }
}
