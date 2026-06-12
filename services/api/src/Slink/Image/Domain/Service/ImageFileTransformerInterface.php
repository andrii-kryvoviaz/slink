<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use SplFileInfo;

interface ImageFileTransformerInterface {
  public function stripExifMetadata(string $path): string;

  /**
   * @template T of SplFileInfo
   *
   * @param T $file
   * @param ImageFormat $format
   * @param int|null $quality
   * @param bool $strip
   * @return T
   */
  public function convertToFormat(SplFileInfo $file, ImageFormat $format, ?int $quality = null, bool $strip = true): SplFileInfo;
}
