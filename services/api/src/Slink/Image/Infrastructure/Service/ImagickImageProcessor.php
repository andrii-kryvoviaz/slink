<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Imagick;
use ImagickException;
use Slink\Image\Domain\Enum\AnimationStrategy;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;
use RuntimeException;

final class ImagickImageProcessor implements ImageProcessorInterface {
  public function convertFormat(string $imageContent, string $format, ?int $quality = null): string {
    try {
      $imagick = new Imagick();
      $imagick->readImageBlob($imageContent);
      $imagick->setImageFormat($format);

      if ($quality !== null) {
        $imagick->setImageCompressionQuality($quality);
      }

      $result = $imagick->getImageBlob();
      $imagick->clear();

      return $result;
    } catch (ImagickException $e) {
      throw new RuntimeException('Failed to convert image format: ' . $e->getMessage(), 0, $e);
    }
  }

  public function crop(
    string $imageContent,
    int    $width,
    int    $height,
    int    $x = 0,
    int    $y = 0
  ): string {
    return $this->processImage(
      $imageContent,
      $width,
      $height,
      fn($imagick) => $imagick->cropImage($width, $height, $x, $y),
      'Failed to crop image'
    );
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

  /**
   * @param string $imageContent
   * @return array{int, int}
   */
  public function getImageDimensions(string $imageContent): array {
    try {
      $imagick = new Imagick();
      $imagick->readImageBlob($imageContent);

      $width = $imagick->getImageWidth();
      $height = $imagick->getImageHeight();

      $imagick->clear();

      return [$width, $height];
    } catch (ImagickException $e) {
      throw new RuntimeException('Failed to get image dimensions: ' . $e->getMessage(), 0, $e);
    }
  }

  public function resize(
    string $imageContent,
    int    $width,
    int    $height
  ): string {
    return $this->processImage(
      $imageContent,
      $width,
      $height,
      fn($imagick) => $imagick->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1),
      'Failed to resize image'
    );
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

  public function supportsAnimation(): bool {
    return true;
  }

  private function processImage(
    string   $imageContent,
    int      $width,
    int      $height,
    callable $operation,
    string   $errorMessage
  ): string {
    try {
      $imagick = new Imagick();
      $imagick->readImageBlob($imageContent);
      $imagick->autoOrient();

      $isAnimated = $imagick->getNumberImages() > 1;
      $shouldPreserveAnimation = $this->shouldPreserveAnimation($isAnimated, AnimationStrategy::AUTO);

      if ($shouldPreserveAnimation && $isAnimated) {
        $imagick = $imagick->coalesceImages();
        foreach ($imagick as $frame) {
          $operation($frame);
          $frame->setImagePage($width, $height, 0, 0);
        }
        $imagick = $imagick->deconstructImages();
        $result = $imagick->getImagesBlob();
      } else {
        if ($isAnimated) {
          $imagick->setIteratorIndex(0);
          $imagick = $imagick->getImage();
        }
        $operation($imagick);
        $result = $imagick->getImageBlob();
      }

      $imagick->clear();
      return $result;
    } catch (ImagickException $e) {
      throw new RuntimeException($errorMessage . ': ' . $e->getMessage(), 0, $e);
    }
  }

  private function shouldPreserveAnimation(bool $isAnimated, AnimationStrategy $strategy): bool {
    return match ($strategy) {
      AnimationStrategy::PRESERVE_ANIMATION => $this->supportsAnimation(),
      AnimationStrategy::FIRST_FRAME_ONLY => false,
      AnimationStrategy::AUTO => $isAnimated && $this->supportsAnimation(),
    };
  }
}
