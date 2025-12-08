<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Exception;
use Jcupitt\Vips\Image as VipsImage;
use Slink\Image\Domain\Enum\ImageFormat;

final class VipsFormatAdapter {
  private const string QUALITY_PARAM = 'Q';
  private const int DEFAULT_ANIMATION_DELAY = 100;
  private const int DEFAULT_ANIMATION_LOOP = 0;

  /** @var array<string, ImageFormat> */
  private const array VIPS_LOADER_MAPPING = [
    'jpegload' => ImageFormat::JPEG,
    'jpegload_buffer' => ImageFormat::JPEG,
    'pngload' => ImageFormat::PNG,
    'pngload_buffer' => ImageFormat::PNG,
    'gifload' => ImageFormat::GIF,
    'gifload_buffer' => ImageFormat::GIF,
    'webpload' => ImageFormat::WEBP,
    'webpload_buffer' => ImageFormat::WEBP,
    'heifload' => ImageFormat::AVIF,
    'heifload_buffer' => ImageFormat::AVIF,
    'tiffload' => ImageFormat::TIFF,
    'tiffload_buffer' => ImageFormat::TIFF,
    'ppmload' => ImageFormat::BMP,
    'ppmload_buffer' => ImageFormat::BMP,
  ];

  /**
   * @return array<string, int>
   */
  public function buildFormatOptions(?int $quality): array {
    return $quality !== null ? [self::QUALITY_PARAM => $quality] : [];
  }

  public function detectFormatFromLoader(string $loader): ImageFormat {
    return self::VIPS_LOADER_MAPPING[$loader] ?? ImageFormat::JPEG;
  }

  public function getBufferFormat(ImageFormat $format): string {
    return '.' . $format->getExtension();
  }

  /**
   * @return array<int, int>
   */
  public function getDefaultAnimationDelays(int $frameCount): array {
    return array_fill(0, $frameCount, self::DEFAULT_ANIMATION_DELAY);
  }

  public function getDefaultAnimationLoop(): int {
    return self::DEFAULT_ANIMATION_LOOP;
  }

  /**
   * @throws Exception
   */
  public function writeAnimatedToBuffer(VipsImage $image, ImageFormat $format): string {
    if (!$this->hasSpecializedAnimatedWriter($format)) {
      return $this->writeToBuffer($image, $format);
    }

    $method = $this->getAnimatedWriteMethod($format);
    $options = $this->getAnimatedOptions($format);

    return $image->$method($options);
  }

  /**
   * @param array<string, mixed> $options
   * @throws Exception
   */
  public function writeToBuffer(VipsImage $image, ImageFormat $format, array $options = []): string {
    return $image->writeToBuffer($this->getBufferFormat($format), $options);
  }

  /**
   * @return array<string, mixed>
   */
  private function getAnimatedOptions(ImageFormat $format): array {
    return match ($format) {
      ImageFormat::GIF => ['dither' => 1.0, 'effort' => 5],
      ImageFormat::WEBP, ImageFormat::AVIF => ['Q' => 75, 'lossless' => false, 'effort' => 4],
      default => []
    };
  }

  private function getAnimatedWriteMethod(ImageFormat $format): string {
    return match ($format) {
      ImageFormat::GIF => 'gifsave_buffer',
      ImageFormat::WEBP => 'webpsave_buffer',
      ImageFormat::AVIF => 'heifsave_buffer',
      default => 'writeToBuffer'
    };
  }

  private function hasSpecializedAnimatedWriter(ImageFormat $format): bool {
    return match ($format) {
      ImageFormat::GIF, ImageFormat::WEBP, ImageFormat::AVIF => true,
      default => false
    };
  }
}
