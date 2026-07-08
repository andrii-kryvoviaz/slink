<?php

declare(strict_types=1);

namespace Slink\Media\Domain\Enum;

enum MediaFormat: string {
  case Png = 'png';
  case Jpeg = 'jpeg';
  case Gif = 'gif';
  case Webp = 'webp';
  case Avif = 'avif';
  case Svg = 'svg';
  case Bmp = 'bmp';
  case Ico = 'ico';
  case Tga = 'tga';
  case Heic = 'heic';
  case Tiff = 'tiff';

  public function bit(): int {
    return match ($this) {
      self::Png => 1 << 0,
      self::Jpeg => 1 << 1,
      self::Gif => 1 << 2,
      self::Webp => 1 << 3,
      self::Avif => 1 << 4,
      self::Svg => 1 << 5,
      self::Bmp => 1 << 6,
      self::Ico => 1 << 7,
      self::Tga => 1 << 8,
      self::Heic => 1 << 9,
      self::Tiff => 1 << 10,
    };
  }

  public function label(): string {
    return match ($this) {
      self::Png => 'PNG',
      self::Jpeg => 'JPEG',
      self::Gif => 'GIF',
      self::Webp => 'WebP',
      self::Avif => 'AVIF',
      self::Svg => 'SVG',
      self::Bmp => 'BMP',
      self::Ico => 'ICO',
      self::Tga => 'TGA',
      self::Heic => 'HEIC',
      self::Tiff => 'TIFF',
    };
  }

  /**
   * @return list<string>
   */
  public function mimeTypes(): array {
    return match ($this) {
      self::Png => ['image/png'],
      self::Jpeg => ['image/jpeg', 'image/jpg'],
      self::Gif => ['image/gif'],
      self::Webp => ['image/webp'],
      self::Avif => ['image/avif'],
      self::Svg => ['image/svg+xml', 'image/svg'],
      self::Bmp => ['image/bmp'],
      self::Ico => ['image/x-icon', 'image/vnd.microsoft.icon'],
      self::Tga => ['image/x-tga'],
      self::Heic => ['image/heic', 'image/heif'],
      self::Tiff => ['image/tiff', 'image/tif'],
    };
  }

  public function mediaType(): MediaType {
    return MediaType::Image;
  }

  /**
   * @return list<string>
   */
  public static function allValues(): array {
    return array_map(static fn (self $format): string => $format->value, self::cases());
  }

  /**
   * @return list<self>
   */
  public static function fromMask(int $mask): array {
    return array_values(array_filter(
      self::cases(),
      static fn (self $format): bool => ($mask & $format->bit()) !== 0,
    ));
  }

  /**
   * @return list<string>
   */
  public static function resolveMimeTypes(int $mask): array {
    $mimeTypes = [];
    foreach (self::fromMask($mask) as $format) {
      foreach ($format->mimeTypes() as $mimeType) {
        $mimeTypes[$mimeType] = true;
      }
    }

    return array_keys($mimeTypes);
  }
}
