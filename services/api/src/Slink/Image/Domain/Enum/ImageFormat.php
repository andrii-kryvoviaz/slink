<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

enum ImageFormat: string {
  case JPEG = 'jpg';
  case PNG = 'png';
  case GIF = 'gif';
  case WEBP = 'webp';
  case AVIF = 'avif';
  case TIFF = 'tiff';
  case BMP = 'bmp';

  public static function fromString(string $format): self {
    return match (strtolower($format)) {
      'jpg', 'jpeg' => self::JPEG,
      'png' => self::PNG,
      'gif' => self::GIF,
      'webp' => self::WEBP,
      'avif', 'heif' => self::AVIF,
      'tiff', 'tif' => self::TIFF,
      'bmp' => self::BMP,
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
      'image/tiff' => self::TIFF,
      'image/bmp', 'image/x-bmp' => self::BMP,
      default => null
    };
  }

  public function getMimeType(): string {
    return match ($this) {
      self::JPEG => 'image/jpeg',
      self::PNG => 'image/png',
      self::GIF => 'image/gif',
      self::WEBP => 'image/webp',
      self::AVIF => 'image/avif',
      self::TIFF => 'image/tiff',
      self::BMP => 'image/bmp',
    };
  }

  public function getExtension(): string {
    return $this->value;
  }

  public function supportsAnimation(): bool {
    return match ($this) {
      self::GIF, self::WEBP => true,
      default => false
    };
  }
}
