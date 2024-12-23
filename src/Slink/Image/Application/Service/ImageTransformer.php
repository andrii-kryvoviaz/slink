<?php

namespace Slink\Image\Application\Service;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Imagick;
use ImagickException;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

final readonly class ImageTransformer implements ImageTransformerInterface {
  public function __construct(
    private SettingsService $settingsService
  ) {
  }
  
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
    } catch (ImagickException $exception) {
      // do nothing hence the image format is not supported
    } finally {
      return $path;
    }
  }
  
  /**
   * @param SplFileInfo $file
   * @param int|null $quality
   * @return File
   * @throws ImagickException
   */
  public function convertToJpeg(SplFileInfo $file, ?int $quality = null): File {
    $image = new Imagick($file->getPathname());
    $image->setImageFormat('jpeg');
    
    $quality ??= $this->settingsService->get('image.compressionQuality');
    
    if ($quality) {
      $image->setImageCompressionQuality($quality);
    }
    
    $fileName = $file->getBasename('.' . $file->getExtension());
    $jpegPath = sprintf('%s/%s.jpg', $file->getPath(), $fileName);
    
    $image->writeImage($jpegPath);
    $image->clear();
    
    return new File($jpegPath, true);
  }
}