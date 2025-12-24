<?php
declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\Image as VipsImage;
use RuntimeException;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Throwable;

#[AsAlias(ImageProcessorInterface::class)]
final class VipsImageProcessor implements ImageProcessorInterface {

  /**
   * @param VipsFormatAdapter $formatAdapter
   */
  public function __construct(private readonly VipsFormatAdapter $formatAdapter)
  {
  }

  /**
   * @param string $imageContent
   * @param string $format
   * @param int|null $quality
   * @return string
   */
  public function convertFormat(string $imageContent, string $format, ?int $quality = null): string {
    try {
      $imageFormat = ImageFormat::fromString($format);
      
      $image = VipsImage::newFromBuffer($imageContent);
      $pages = $this->getImagePages($image);
      $isAnimated = $pages > 1;
      
      if ($isAnimated && $imageFormat->supportsAnimation()) {
        $image = VipsImage::newFromBuffer($imageContent, '', ['n' => -1]);
        return $this->convertAnimatedFormat($imageContent, $imageFormat, $pages);
      }
      
      return $this->formatAdapter->writeToBuffer(
        $image,
        $imageFormat,
        $this->formatAdapter->buildFormatOptions($quality)
      );
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to convert image format: ' . $e->getMessage(), 0, $e);
    }
  }

  private function convertAnimatedFormat(string $imageContent, ImageFormat $format, int $pages): string {
    $delays = $this->getImageDelays($imageContent, $pages);
    $frames = $this->processAnimatedFrames($imageContent, fn(VipsImage $frame) => $frame, $pages);
    $combined = $this->combineAnimatedFrames($frames, $delays);
    
    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format);
  }

  /**
   * @param string $imageContent
   * @param int $width
   * @param int $height
   * @param int $x
   * @param int $y
   * @return string
   */
  public function crop(string $imageContent, int $width, int $height, int $x = 0, int $y = 0): string {
    return $this->processImage(
      $imageContent,
      fn(VipsImage $image) => $image->crop($x, $y, $width, $height)
    );
  }

  /**
   * @param string $imageContent
   * @return AnimatedImageInfo
   */
  public function getAnimatedImageInfo(string $imageContent): AnimatedImageInfo {
    try {
      $image = VipsImage::newFromBuffer($imageContent);
      $count = $this->getImagePages($image);
      return $count > 1 
        ? AnimatedImageInfo::animated($count)
        : AnimatedImageInfo::static();
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to get animated image info: ' . $e->getMessage(), 0, $e);
    }
  }

  /**
   * @param string $imageContent
   * @return array{int, int}
   */
  public function getImageDimensions(string $imageContent): array {
    try {
      $image = VipsImage::newFromBuffer($imageContent);
      return [$image->width, $image->height];
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to get image dimensions: ' . $e->getMessage(), 0, $e);
    }
  }

  /**
   * @param string $imageContent
   * @param int $width
   * @param int $height
   * @return string
   */
  public function resize(string $imageContent, int $width, int $height): string {
    return $this->processImage(
      $imageContent,
      fn(VipsImage $image) => $image->resize(min($width / $image->width, $height / $image->height))
    );
  }

  /**
   * @param string $path
   * @return string
   */
  public function stripMetadata(string $path): string {
    try {
      $image = VipsImage::newFromFile($path);
      $mimeType = mime_content_type($path);

      if ($mimeType === false) {
        return $path;
      }

      $imageFormat = ImageFormat::fromMimeType($mimeType);

      if ($imageFormat === null) {
        return $path;
      }

      $image = $image->autorot();
      
      $extension = $imageFormat->getExtension();

      $image->writeToFile("$path.$extension", ['strip' => true]);
      rename("$path.$extension", $path);
      
      return $path;
    } catch (Throwable) {
      return $path;
    }
  }

  /**
   * @param string $buf
   * @param callable $operation
   * @return string
   */
  private function processImage(string $buf, callable $operation): string {
    $this->validateImageContent($buf);
    
    try {
      $img = VipsImage::newFromBuffer($buf);
      $originalFormat = $this->formatAdapter->detectFormatFromLoader($img->get('vips-loader'));
      $pages = $this->getImagePages($img);
      $this->validateImagePages($pages);
      
      return $this->isStaticImage($pages) 
        ? $this->processStaticImage($img, $operation, $originalFormat)
        : $this->processAnimatedImage($buf, $operation, $originalFormat, $pages);
    } catch (Throwable $e) {
      throw new RuntimeException($e->getMessage(), 0, $e);
    }
  }

  /**
   * @param string $buf
   * @return void
   */
  private function validateImageContent(string $buf): void {
    if ($buf === '') {
      throw new RuntimeException("Empty content");
    }
  }

  /**
   * @param VipsImage $img
   * @return int
   */
  private function getImagePages(VipsImage $img): int {
    try {
      return (int) $img->get('n-pages');
    } catch (Throwable) {
      return 1;
    }
  }

  /**
   * @param int $pages
   * @return void
   */
  private function validateImagePages(int $pages): void {
    if ($pages < 1) {
      throw new RuntimeException("No pages");
    }
  }

  /**
   * @param int $pages
   * @return bool
   */
  private function isStaticImage(int $pages): bool {
    return $pages === 1;
  }

  /**
   * @param VipsImage $img
   * @param callable $operation
   * @param ImageFormat $format
   * @return string
   */
  private function processStaticImage(VipsImage $img, callable $operation, ImageFormat $format): string {
    return $this->formatAdapter->writeToBuffer($operation($img), $format);
  }

  /**
   * @param string $buf
   * @param callable $operation
   * @param ImageFormat $format
   * @param int $pages
   * @return string
   */
  private function processAnimatedImage(string $buf, callable $operation, ImageFormat $format, int $pages): string {
    $delays = $this->getImageDelays($buf, $pages);
    $frames = $this->processAnimatedFrames($buf, $operation, $pages);
    $combined = $this->combineAnimatedFrames($frames, $delays);

    return $this->writeAnimatedImage($combined, $format);
  }

  /**
   * @param string $buf
   * @param int $pages
   * @return array<int, int>
   */
  private function getImageDelays(string $buf, int $pages): array {
    try {
      $img = VipsImage::newFromBuffer($buf);
      return $img->get('delay');
    } catch (Throwable) {
      return $this->formatAdapter->getDefaultAnimationDelays($pages);
    }
  }

  /**
   * @param string $buf
   * @param callable $operation
   * @param int $pages
   * @return array<int, VipsImage>
   * @throws VipsException
   */
  private function processAnimatedFrames(string $buf, callable $operation, int $pages): array {
    $frames = [];
    for ($i = 0; $i < $pages; $i++) {
      $frame = VipsImage::newFromBuffer($buf, '', ['page' => $i]);
      $frames[] = $operation($frame);
    }
    return $frames;
  }

  /**
   * @param array<int, VipsImage> $frames
   * @param array<int, int> $delays
   * @return VipsImage
   * @throws VipsException
   */
  private function combineAnimatedFrames(array $frames, array $delays): VipsImage {
    if (empty($frames)) {
      throw new RuntimeException('No frames to combine');
    }
    
    $first = array_shift($frames);
    $frameHeight = $first->height;

    foreach ($frames as $frame) {
      $first = $first->join($frame, 'vertical');
    }

    $first->set('delay', $delays);
    $first->set('loop', $this->formatAdapter->getDefaultAnimationLoop());
    $first->set('page-height', $frameHeight);
    
    return $first;
  }

  /**
   * @param VipsImage $combined
   * @param ImageFormat $format
   * @return string
   * @throws VipsException
   */
  private function writeAnimatedImage(VipsImage $combined, ImageFormat $format): string {
    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format);
  }
}
