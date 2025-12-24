<?php

namespace Slink\Image\Domain\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;

interface ImageTransformerInterface {
  /**
   * @param string $content
   * @param int|null $width
   * @param int|null $height
   * @return string
   */
  public function resize(string $content, ?int $width, ?int $height): string;
  
  /**
   * @param string $content
   * @param int|null $width
   * @param int|null $height
   * @return string
   */
  public function crop(string $content, ?int $width, ?int $height): string;
  
  /**
   * @param string $content
   * @param ImageOptions $imageOptions
   * @return string
   */
  public function transform(string $content, ImageOptions $imageOptions): string;
  
  /**
   * @param string $path
   * @return string
   */
  public function stripExifMetadata(string $path): string;
  
  /**
   * @template T of SplFileInfo
   *
   * @param T $file
   * @param int|null $quality
   * @return T
   */
  public function convertToJpeg(SplFileInfo $file, ?int $quality = null): SplFileInfo;
  
  /**
   * @template T of SplFileInfo
   *
   * @param T $file
   * @param ImageFormat $format
   * @param int|null $quality
   * @return T
   */
  public function convertToFormat(SplFileInfo $file, ImageFormat $format, ?int $quality = null): SplFileInfo;
}