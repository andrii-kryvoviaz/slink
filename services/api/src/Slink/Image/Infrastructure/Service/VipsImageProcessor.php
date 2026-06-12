<?php
declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\Image as VipsImage;
use RuntimeException;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileProcessorInterface;
use Slink\Image\Domain\Service\ImageInspectorInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageSource;
use Slink\Image\Domain\ValueObject\AnimatedImageInfo;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Slink\Image\Infrastructure\Service\Operation\VipsContext;
use Slink\Image\Infrastructure\Service\Operation\VipsOperationApplierRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Throwable;

#[AsAlias(ImageProcessorInterface::class)]
#[AsAlias(ImageFileProcessorInterface::class)]
#[AsAlias(ImageInspectorInterface::class)]
final class VipsImageProcessor implements ImageProcessorInterface, ImageFileProcessorInterface, ImageInspectorInterface {
  public function __construct(
    private readonly VipsFormatAdapter $formatAdapter,
    private readonly VipsOperationApplierRegistry $applierRegistry
  ) {
  }

  public function process(
    ImageSource  $source,
    array        $operations,
    ?ImageFormat $format = null,
    ?int         $quality = null,
    bool         $strip = false
  ): string {
    try {
      $vipsSource = new VipsImageSource($source);

      $probe = $vipsSource->load();
      $pages = $this->getImagePages($probe);
      $resolvedFormat = $format ?? $this->formatAdapter->detectFormatFromLoader($probe->get('vips-loader'));
      $animated = $pages > 1 && $resolvedFormat->supportsAnimation();

      if ($animated) {
        $image = $vipsSource->load(['access' => 'sequential', 'n' => -1]);

        return $this->processAnimated($image, $operations, $resolvedFormat, $pages, $quality);
      }

      $context = VipsContext::forSource($vipsSource);
      $this->applyOperations($context, $operations);
      $image = $context->result();

      $options = $this->formatAdapter->buildFormatOptions($resolvedFormat, $quality)
        + ($strip ? ['strip' => true] : []);

      return $this->formatAdapter->writeToBuffer($image, $resolvedFormat, $options);
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to process image: ' . $e->getMessage(), 0, $e);
    }
  }

  /**
   * @param ImageOperation[] $operations
   */
  private function applyOperations(VipsContext $context, array $operations): void {
    foreach ($operations as $operation) {
      $this->applierRegistry->applierFor($operation)?->apply($context, $operation);
    }
  }

  /**
   * @param ImageOperation[] $operations
   */
  private function processAnimated(
    VipsImage   $animated,
    array       $operations,
    ImageFormat $format,
    int         $pages,
    ?int        $quality
  ): string {
    $delays = $this->getAnimatedDelays($animated, $pages);
    $frames = $this->extractAnimatedFrames(
      $animated,
      function (VipsImage $frame) use ($operations): VipsImage {
        $context = VipsContext::forFrame($frame);
        $this->applyOperations($context, $operations);

        return $context->result();
      },
      $pages
    );
    $combined = $this->combineAnimatedFrames($frames, $delays);

    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format, $quality);
  }

  /**
   * @param callable(VipsImage): VipsImage $operation
   * @return array<int, VipsImage>
   * @throws VipsException
   */
  private function extractAnimatedFrames(VipsImage $animated, callable $operation, int $pages): array {
    $frameHeight = $this->getFrameHeight($animated, $pages);

    $frames = [];
    for ($i = 0; $i < $pages; $i++) {
      $frame = $animated->crop(0, $i * $frameHeight, $animated->width, $frameHeight);
      $frames[] = $operation($frame);
    }

    return $frames;
  }

  private function getFrameHeight(VipsImage $animated, int $pages): int {
    try {
      return (int) $animated->get('page-height');
    } catch (Throwable) {
      return \intdiv($animated->height, $pages);
    }
  }

  /**
   * @return array<int, int>
   */
  private function getAnimatedDelays(VipsImage $animated, int $pages): array {
    try {
      return $animated->get('delay');
    } catch (Throwable) {
      return $this->formatAdapter->getDefaultAnimationDelays($pages);
    }
  }

  public function convertFormatFile(string $sourcePath, string $targetPath, string $format, ?int $quality = null, bool $strip = true): void {
    $this->doConvertFile($sourcePath, $targetPath, ImageFormat::fromString($format), $quality, $strip);
  }

  private function doConvertFile(string $sourcePath, string $targetPath, ImageFormat $format, ?int $quality, bool $strip): void {
    try {
      $source = VipsImage::newFromFile($sourcePath, ['access' => 'sequential']);
      $pages = $this->getImagePages($source);

      if ($pages > 1 && $format->supportsAnimation()) {
        file_put_contents($targetPath, $this->convertAnimatedFormat($sourcePath, $format, $pages, $quality));

        return;
      }

      if ($strip) {
        $source = $this->withSensitiveMetadataRemoved($source);
      }

      $this->formatAdapter->writeToFile(
        $source,
        $targetPath,
        $this->formatAdapter->buildFormatOptions($format, $quality)
      );
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to convert image format: ' . $e->getMessage(), 0, $e);
    }
  }

  private function convertAnimatedFormat(string $sourcePath, ImageFormat $format, int $pages, ?int $quality): string {
    $animated = VipsImage::newFromFile($sourcePath, ['access' => 'sequential', 'n' => -1]);
    $delays = $this->getAnimatedDelays($animated, $pages);
    $frames = $this->extractAnimatedFrames($animated, fn(VipsImage $frame): VipsImage => $frame, $pages);
    $combined = $this->combineAnimatedFrames($frames, $delays);

    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format, $quality);
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
   * @param string $path
   * @return string
   */
  public function stripMetadata(string $path): string {
    try {
      $image = VipsImage::newFromFile($path, ['access' => 'sequential']);
      $mimeType = mime_content_type($path);

      if ($mimeType === false) {
        return $path;
      }

      $imageFormat = ImageFormat::fromMimeType($mimeType);

      if ($imageFormat === null) {
        return $path;
      }

      $image = $this->withSensitiveMetadataRemoved($image);
      $extension = $imageFormat->getExtension();

      $image->writeToFile("$path.$extension");
      rename("$path.$extension", $path);

      return $path;
    } catch (Throwable) {
      return $path;
    }
  }

  private function withSensitiveMetadataRemoved(VipsImage $image): VipsImage {
    $image = $image->copy();
    $preserved = [
      'orientation',
      'icc-profile-data',
      'n-pages',
      'page-height',
      'delay',
      'loop',
      'background',
      ...VipsImage::black(1, 1)->getFields(),
    ];

    foreach ($image->getFields() as $field) {
      if (!\in_array($field, $preserved, true)) {
        $image->remove($field);
      }
    }

    return $image;
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
}
