<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use RuntimeException;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageDimensions;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\File\File;

final readonly class ImageTransformer implements ImageTransformerInterface {
  /**
   * @param ImageProcessorInterface $imageProcessor
   * @param SettingsService $settingsService
   * @param iterable<ImageTransformationStrategyInterface> $strategies
   */
  public function __construct(
    private ImageProcessorInterface $imageProcessor,
    private SettingsService         $settingsService,
    #[AutowireIterator('image.transformation_strategy')]
    private iterable                $strategies
  ) {
  }

  public function convertToJpeg(SplFileInfo $file, ?int $quality = null): File {
    $content = file_get_contents($file->getPathname());
    if ($content === false) {
      throw new RuntimeException('Failed to read file content');
    }

    $quality ??= $this->settingsService->get('image.compressionQuality');

    $convertedContent = $this->imageProcessor->convertFormat(
      $content,
      'jpeg',
      $quality
    );

    $fileName = $file->getBasename('.' . $file->getExtension());
    $jpegPath = sprintf('%s/%s.jpg', $file->getPath(), $fileName);

    file_put_contents($jpegPath, $convertedContent);

    return new File($jpegPath, true);
  }

  public function crop(string $content, ?int $width, ?int $height): string {
    return $this->performTransformation($content, $width, $height, crop: true);
  }

  public function executeTransformation(
    string                     $content,
    ImageTransformationRequest $request
  ): string {
    [$width, $height] = $this->imageProcessor->getImageDimensions($content);
    $originalDimensions = new ImageDimensions($width, $height);

    foreach ($this->strategies as $strategy) {
      if ($strategy->supports($request)) {
        return $strategy->transform(
          $content,
          $originalDimensions,
          $request
        );
      }
    }

    return $content;
  }

  public function resize(string $content, ?int $width, ?int $height): string {
    return $this->performTransformation($content, $width, $height, crop: false);
  }

  public function stripExifMetadata(string $path): string {
    return $this->imageProcessor->stripMetadata($path);
  }

  public function transform(string $content, ImageOptions $imageOptions): string {
    $request = ImageTransformationRequest::fromImageOptions($imageOptions);

    if (!$request->hasTransformations()) {
      return $content;
    }

    return $this->executeTransformation($content, $request);
  }

  private function calculateTargetDimensions(
    string $content,
    ?int   $width,
    ?int   $height
  ): ImageDimensions {
    [$originalWidth, $originalHeight] = $this->imageProcessor->getImageDimensions($content);
    $originalDimensions = new ImageDimensions($originalWidth, $originalHeight);

    return ImageDimensions::createWithAspectRatio($width, $height, $originalDimensions);
  }

  private function performTransformation(
    string $content,
    ?int   $width,
    ?int   $height,
    bool   $crop
  ): string {
    if ($width === null && $height === null) {
      return $content;
    }

    $calculatedDimensions = $this->calculateTargetDimensions($content, $width, $height);

    $request = new ImageTransformationRequest(
      targetDimensions: $calculatedDimensions,
      crop: $crop
    );

    return $this->executeTransformation($content, $request);
  }
}