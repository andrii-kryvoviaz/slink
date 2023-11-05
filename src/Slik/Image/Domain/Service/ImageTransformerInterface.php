<?php

namespace Slik\Image\Domain\Service;

interface ImageTransformerInterface {
  public function resizeFromString(string $content, int $width, int $height): string;
  
  public function transform(string $content, array $payload): string;
}