<?php

namespace Slik\Image\Infrastructure\Service;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Slik\Image\Domain\Service\ImageTransformerInterface;

class ImageTransformer implements ImageTransformerInterface {
  
  /**
   * @return self
   */
  public static function create(): self {
    return new self();
  }
  
  /**
   * @param string $content
   * @param int|null $width
   * @param int|null $height
   * @return string
   * @throws ImageResizeException
   */
  public function resizeFromString(string $content, ?int $width, ?int $height): string {
    $image = ImageResize::createFromString($content);

    if ($width === null && $height === null) {
      return $content;
    }
    
    if ($width === null) {
      return $image->resizeToHeight($height)->getImageAsString();
    }
    
    if ($height === null) {
      return $image->resizeToWidth($width)->getImageAsString();
    }
    
    return $image->resizeToBestFit($width, $height)->getImageAsString();
  }
  
  /**
   * @param string $content
   * @param array<string, mixed> $payload
   * @throws ImageResizeException
   */
  public function transform(string $content, array $payload): string {
    if($payload['width'] || $payload['height']) {
      $content = $this->resizeFromString($content, $payload['width'], $payload['height']);
    }
    
    return $content;
  }
}