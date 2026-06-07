<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Imagick;
use ImagickException;
use Slink\Image\Domain\Enum\ImageFilter;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileProcessorInterface;
use Slink\Image\Domain\Service\ImageInspectorInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageSource;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;
use Slink\Image\Domain\ValueObject\Operation\Cover;
use Slink\Image\Domain\ValueObject\Operation\Filter;
use Slink\Image\Domain\ValueObject\Operation\Fit;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use RuntimeException;

final class ImagickImageProcessor implements ImageProcessorInterface, ImageFileProcessorInterface, ImageInspectorInterface {
  public function process(
    ImageSource  $source,
    array        $operations,
    ?ImageFormat $format = null,
    ?int         $quality = null,
    bool         $strip = false
  ): string {
    try {
      $imagick = new Imagick();
      $this->readSource($imagick, $source);
      $imagick->autoOrient();

      $this->applyOperations($imagick, $operations);

      if ($format !== null) {
        $imagick->setImageFormat($format->value);
      }

      if ($quality !== null) {
        $imagick->setImageCompressionQuality($quality);
      }

      if ($strip) {
        $imagick->stripImage();
      }

      $result = $imagick->getImagesBlob();
      $imagick->clear();

      return $result;
    } catch (ImagickException $e) {
      throw new RuntimeException('Failed to process image: ' . $e->getMessage(), 0, $e);
    }
  }

  private function readSource(Imagick $imagick, ImageSource $source): void {
    if ($source->hasLocalPath()) {
      $imagick->readImage($source->getLocalPath());

      return;
    }

    $imagick->readImageBlob($source->readBytes());
  }

  /**
   * @param ImageOperation[] $operations
   */
  private function applyOperations(Imagick $imagick, array $operations): void {
    foreach ($operations as $operation) {
      match (true) {
        $operation instanceof Fit => $this->applyFit($imagick, $operation),
        $operation instanceof Cover => $imagick->cropThumbnailImage($operation->width, $operation->height),
        $operation instanceof Filter => $this->applyFilterToImagick($imagick, $operation->name),
        default => null,
      };
    }
  }

  private function applyFit(Imagick $imagick, Fit $fit): void {
    $width = $fit->width ?? 0;
    $height = $fit->height ?? 0;

    if (!$fit->allowEnlarge) {
      $width = $width === 0 ? 0 : min($width, $imagick->getImageWidth());
      $height = $height === 0 ? 0 : min($height, $imagick->getImageHeight());
    }

    $imagick->thumbnailImage($width, $height, true);
  }

  private function applyFilterToImagick(Imagick $imagick, string $filter): void {
    $imageFilter = ImageFilter::tryFromString($filter);

    if ($imageFilter === null) {
      return;
    }

    match ($imageFilter) {
      ImageFilter::Dramatic => $this->applyDramaticFilter($imagick),
      ImageFilter::Noir => $this->applyNoirFilter($imagick),
      ImageFilter::Sepia => $this->applySepiaFilter($imagick),
      ImageFilter::Warm => $this->applyColorTint($imagick, 1.06, 1.0, 0.92),
      ImageFilter::Cool => $this->applyColorTint($imagick, 0.92, 1.0, 1.08),
      ImageFilter::Vivid => $imagick->modulateImage(100, 140, 100),
      ImageFilter::Fade => $this->applyFadeFilter($imagick),
    };
  }

  public function convertFormatFile(string $sourcePath, string $targetPath, string $format, ?int $quality = null, bool $strip = true): void {
    $this->doConvertFile($sourcePath, $targetPath, $format, $quality, $strip);
  }

  private function doConvertFile(string $sourcePath, string $targetPath, string $format, ?int $quality, bool $strip): void {
    try {
      $imagick = new Imagick();
      $imagick->readImage($sourcePath);
      $imagick->setImageFormat($format);

      if ($quality !== null) {
        $imagick->setImageCompressionQuality($quality);
      }

      if ($strip) {
        $imagick->stripImage();
      }

      $imagick->writeImage($targetPath);
      $imagick->clear();
    } catch (ImagickException $e) {
      throw new RuntimeException('Failed to convert image format: ' . $e->getMessage(), 0, $e);
    }
  }

  public function getAnimatedImageInfo(string $imageContent): AnimatedImageInfo {
    try {
      $imagick = new Imagick();
      $imagick->readImageBlob($imageContent);

      $frameCount = $imagick->getNumberImages();
      $isAnimated = $frameCount > 1;

      $imagick->clear();

      return $isAnimated
        ? AnimatedImageInfo::animated($frameCount)
        : AnimatedImageInfo::static();
    } catch (ImagickException $e) {
      throw new RuntimeException('Failed to get animated image info: ' . $e->getMessage(), 0, $e);
    }
  }

  private function applyDramaticFilter(Imagick $imagick): void {
    $imagick->modulateImage(100, 70, 100);
    $imagick->brightnessContrastImage(-10, 40);
  }

  private function applyNoirFilter(Imagick $imagick): void {
    $imagick->modulateImage(100, 0, 100);
    $imagick->brightnessContrastImage(-8, 30);
  }

  private function applySepiaFilter(Imagick $imagick): void {
    $imagick->modulateImage(100, 0, 100);
    $imagick->colorizeImage('#704214', 1.0);
  }

  private function applyColorTint(Imagick $imagick, float $red, float $green, float $blue): void {
    $imagick->evaluateImage(Imagick::EVALUATE_MULTIPLY, $red, Imagick::CHANNEL_RED);
    $imagick->evaluateImage(Imagick::EVALUATE_MULTIPLY, $green, Imagick::CHANNEL_GREEN);
    $imagick->evaluateImage(Imagick::EVALUATE_MULTIPLY, $blue, Imagick::CHANNEL_BLUE);
  }

  private function applyFadeFilter(Imagick $imagick): void {
    $imagick->brightnessContrastImage(8, -15);
  }

  public function stripMetadata(string $path): string {
    try {
      $imagick = new Imagick($path);
      $imagick->autoOrient();

      $profiles = $imagick->getImageProfiles('icc');
      $imagick->stripImage();

      if (!empty($profiles)) {
        $imagick->profileImage("icc", $profiles['icc']);
      }

      $imagick->writeImage($path);
      $imagick->clear();

      return $path;
    } catch (ImagickException) {
      return $path;
    }
  }
}
