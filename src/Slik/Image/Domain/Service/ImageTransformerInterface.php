<?php

namespace Slik\Image\Domain\Service;

interface ImageTransformerInterface {
  /**
   * @param string $content
   * @param int $width
   * @param int $height
   * @return string
   */
  public function resizeFromString(string $content, int $width, int $height): string;
  
  /**
   * @param string $content
   * @param array<string, mixed> $payload
   * @return string
   */
  public function transform(string $content, array $payload): string;
}