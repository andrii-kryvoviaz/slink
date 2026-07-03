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
   * Resolves format values to a de-duplicated list of MIME types.
   *
   * Unknown values are skipped silently; an empty input falls back to the MIME
   * union of every known format so resolution never yields an empty allow-list.
   *
   * @param array<array-key, mixed> $formatValues
   * @return list<string>
   */
  public static function resolveMimeTypes(array $formatValues): array {
    $formats = $formatValues === [] ? self::cases() : self::fromValues($formatValues);

    $mimeTypes = [];
    foreach ($formats as $format) {
      foreach ($format->mimeTypes() as $mimeType) {
        $mimeTypes[$mimeType] = true;
      }
    }

    return array_keys($mimeTypes);
  }

  /**
   * @param array<array-key, mixed> $formatValues
   * @return list<self>
   */
  private static function fromValues(array $formatValues): array {
    $formats = [];
    foreach ($formatValues as $value) {
      $format = \is_string($value) ? self::tryFrom($value) : null;
      if ($format !== null) {
        $formats[] = $format;
      }
    }

    return $formats;
  }
}
