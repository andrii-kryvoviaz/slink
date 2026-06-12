<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\ValueObject\ImageConversionOptions;
use SplFileInfo;

interface ImageFileTransformerInterface {
  public function stripExifMetadata(string $path): string;

  /**
   * @template T of SplFileInfo
   *
   * @param T $file
   * @param ImageConversionOptions $options
   * @return T
   */
  public function convertToFormat(SplFileInfo $file, ImageConversionOptions $options): SplFileInfo;
}
