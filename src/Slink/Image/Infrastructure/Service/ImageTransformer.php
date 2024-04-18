<?php

namespace Slink\Image\Infrastructure\Service;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Imagick;
use ImagickException;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;

class ImageTransformer implements ImageTransformerInterface {
  /**
   * @param string $content
   * @param int|null $width
   * @param int|null $height
   * @return string
   * @throws ImageResizeException
   */
  public function resize(string $content, ?int $width, ?int $height): string {
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
   * @param int|null $width
   * @param int|null $height
   * @return string
   * @throws ImageResizeException
   */
  public function crop(string $content, ?int $width, ?int $height): string {
    $image = ImageResize::createFromString($content);
    
    if (!$width) {
      $width = (int) $height;
    }
    
    if (!$height) {
      $height = (int) $width;
    }
    
    if(!$width && !$height) {
      return $content;
    }
    
    return $image->crop($width, $height)->getImageAsString();
  }
  
  /**
   * @param string $content
   * @param ImageOptions $imageOptions
   * @return string
   */
  public function transform(string $content, ImageOptions $imageOptions): string {
    
    if($imageOptions->getWidth() || $imageOptions->getHeight()) {
      $methodName = $imageOptions->isCropped()
        ? 'crop'
        : 'resize';
      
      $content = $this->{$methodName}($content, $imageOptions->getWidth(), $imageOptions->getHeight());
    }
    
    return $content;
  }
  
  /**
   * @param string $path
   * @return string
   */
  public function stripExifMetadata(string $path): string {
    try {
      $image = new Imagick($path);
      $image->autoOrient();
      
      $profiles = $image->getImageProfiles('icc');
      $image->stripImage();
      
      if (!empty($profiles)) {
        $image->profileImage("icc", $profiles['icc']);
      }
      
      $image->writeImage($path);
      
      $image->clear();
      $image->destroy();
    } catch (ImagickException $exception) {
      // do nothing hence the image format is not supported
    } finally {
      return $path;
    }
  }
}