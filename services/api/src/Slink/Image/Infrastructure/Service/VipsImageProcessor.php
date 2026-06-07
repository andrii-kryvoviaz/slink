<?php
declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\Image as VipsImage;
use RuntimeException;
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
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Throwable;

#[AsAlias(ImageProcessorInterface::class)]
#[AsAlias(ImageFileProcessorInterface::class)]
#[AsAlias(ImageInspectorInterface::class)]
final class VipsImageProcessor implements ImageProcessorInterface, ImageFileProcessorInterface, ImageInspectorInterface {
  private const int LARGE_DIMENSION = 1000000;

  /**
   * @param VipsFormatAdapter $formatAdapter
   */
  public function __construct(private readonly VipsFormatAdapter $formatAdapter)
  {
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
      $geometry = $this->findGeometry($operations);

      $probe = $vipsSource->load();
      $pages = $this->getImagePages($probe);
      $resolvedFormat = $format ?? $this->formatAdapter->detectFormatFromLoader($probe->get('vips-loader'));
      $animated = $pages > 1 && $resolvedFormat->supportsAnimation();

      $image = $this->loadForGeometry($vipsSource, $geometry, $animated);

      if ($animated) {
        return $this->processAnimated($image, $operations, $resolvedFormat, $pages);
      }

      $image = $this->applyFilters($image, $operations);

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
  private function findGeometry(array $operations): Fit|Cover|null {
    foreach ($operations as $operation) {
      if ($operation instanceof Fit || $operation instanceof Cover) {
        return $operation;
      }
    }

    return null;
  }

  private function loadForGeometry(VipsImageSource $vipsSource, Fit|Cover|null $geometry, bool $animated): VipsImage {
    if ($geometry === null) {
      return $vipsSource->load($animated ? ['access' => 'sequential', 'n' => -1] : ['access' => 'sequential']);
    }

    if ($geometry instanceof Fit) {
      return $vipsSource->loadThumbnail($this->fitWidth($geometry), $this->fitOptions($geometry), $animated);
    }

    return $vipsSource->loadThumbnail($geometry->width, [
      'height' => $geometry->height,
      'crop' => 'centre',
      'size' => 'both',
    ], $animated);
  }

  private function fitWidth(Fit $fit): int {
    return $fit->width ?? self::LARGE_DIMENSION;
  }

  /**
   * @return array<string, mixed>
   */
  private function fitOptions(Fit $fit): array {
    return [
      'height' => $fit->height ?? self::LARGE_DIMENSION,
      'size' => $fit->allowEnlarge ? 'both' : 'down',
    ];
  }

  /**
   * @param ImageOperation[] $operations
   */
  private function applyFilters(VipsImage $image, array $operations): VipsImage {
    foreach ($operations as $operation) {
      if ($operation instanceof Filter) {
        $image = $this->applyFilterByName($image, $operation->name);
      }
    }

    return $image;
  }

  private function applyFilterByName(VipsImage $image, string $filter): VipsImage {
    $imageFilter = ImageFilter::tryFromString($filter);

    if ($imageFilter === null) {
      return $image;
    }

    return $this->applyFilterOperation($image, $imageFilter);
  }

  /**
   * @param ImageOperation[] $operations
   */
  private function processAnimated(
    VipsImage   $animated,
    array       $operations,
    ImageFormat $format,
    int         $pages
  ): string {
    $delays = $this->getAnimatedDelays($animated, $pages);
    $frames = $this->extractAnimatedFrames(
      $animated,
      fn(VipsImage $frame): VipsImage => $this->applyFilters($frame, $operations),
      $pages
    );
    $combined = $this->combineAnimatedFrames($frames, $delays);

    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format);
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
        file_put_contents($targetPath, $this->convertAnimatedFormat($sourcePath, $format, $pages));

        return;
      }

      $this->formatAdapter->writeToFile(
        $source,
        $targetPath,
        $this->formatAdapter->buildFormatOptions($format, $quality) + ($strip ? ['strip' => true] : [])
      );
    } catch (Throwable $e) {
      throw new RuntimeException('Failed to convert image format: ' . $e->getMessage(), 0, $e);
    }
  }

  private function convertAnimatedFormat(string $sourcePath, ImageFormat $format, int $pages): string {
    $animated = VipsImage::newFromFile($sourcePath, ['access' => 'sequential', 'n' => -1]);
    $delays = $this->getAnimatedDelays($animated, $pages);
    $frames = $this->extractAnimatedFrames($animated, fn(VipsImage $frame): VipsImage => $frame, $pages);
    $combined = $this->combineAnimatedFrames($frames, $delays);

    return $this->formatAdapter->writeAnimatedToBuffer($combined, $format);
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

  private function applyFilterOperation(VipsImage $image, ImageFilter $filter): VipsImage {
    return match ($filter) {
      ImageFilter::Dramatic => $this->applyDramaticFilter($image),
      ImageFilter::Noir => $this->applyNoirFilter($image),
      ImageFilter::Sepia => $this->applyRecombFilter($image, [
        [0.393, 0.769, 0.189],
        [0.349, 0.686, 0.168],
        [0.272, 0.534, 0.131],
      ]),
      ImageFilter::Warm => $this->applyRecombFilter($image, [
        [1.06, 0.1, 0.0],
        [0.0, 1.0, 0.0],
        [0.0, 0.0, 0.92],
      ]),
      ImageFilter::Cool => $this->applyRecombFilter($image, [
        [0.92, 0.0, 0.0],
        [0.0, 1.0, 0.05],
        [0.0, 0.05, 1.08],
      ]),
      ImageFilter::Vivid => $this->applyRecombFilter($image, $this->buildSaturationMatrix(1.3)),
      ImageFilter::Fade => $image->linear([0.85], [20]),
    };
  }

  /**
   * @param VipsImage $image
   * @param array<int, array<int, float>> $matrix
   * @return VipsImage
   */
  private function applyRecombFilter(VipsImage $image, array $matrix): VipsImage {
    $image = $this->ensureSrgb($image);
    $alpha = null;

    if ($image->hasAlpha()) {
      $alpha = $image->extract_band($image->bands - 1);
      $image = $image->extract_band(0, ['n' => $image->bands - 1]);
    }

    $image = $image->recomb(VipsImage::newFromArray($matrix));

    if ($alpha !== null) {
      $image = $image->bandjoin($alpha);
    }

    return $image;
  }

  private function applyDramaticFilter(VipsImage $image): VipsImage {
    $image = $this->applyRecombFilter($image, $this->buildSaturationMatrix(0.7));
    return $image->linear([1.4], [-30]);
  }

  private function applyNoirFilter(VipsImage $image): VipsImage {
    $image = $image->colourspace('b-w');
    return $image->linear([1.3], [-20]);
  }

  private function ensureSrgb(VipsImage $image): VipsImage {
    $interpretation = $image->interpretation;

    if ($interpretation !== 'srgb' && $interpretation !== 'rgb') {
      return $image->colourspace('srgb');
    }

    return $image;
  }

  /**
   * @param float $amount
   * @return array<int, array<int, float>>
   */
  private function buildSaturationMatrix(float $amount): array {
    $lumaR = 0.3086;
    $lumaG = 0.6094;
    $lumaB = 0.0820;

    return [
      [$lumaR * (1 - $amount) + $amount, $lumaG * (1 - $amount), $lumaB * (1 - $amount)],
      [$lumaR * (1 - $amount), $lumaG * (1 - $amount) + $amount, $lumaB * (1 - $amount)],
      [$lumaR * (1 - $amount), $lumaG * (1 - $amount), $lumaB * (1 - $amount) + $amount],
    ];
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
