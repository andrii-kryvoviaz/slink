<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageFileProcessorInterface;
use Slink\Image\Domain\Service\ImageFileTransformerInterface;
use Slink\Image\Domain\Service\ImageProcessorInterface;
use Slink\Image\Domain\Service\ImageSource;
use Slink\Image\Domain\Service\ImageTransformationStrategyInterface;
use Slink\Image\Domain\Service\ImageTransformerInterface;
use Slink\Image\Domain\ValueObject\ImageTransformationRequest;
use Slink\Image\Domain\ValueObject\Operation\ImageOperation;
use Slink\Settings\Application\Service\SettingsService;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use SplFileInfo;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\File\File;

#[AsAlias(ImageTransformerInterface::class)]
#[AsAlias(ImageFileTransformerInterface::class)]
final readonly class ImageTransformer implements ImageTransformerInterface, ImageFileTransformerInterface {
  /**
   * @param ImageProcessorInterface $imageProcessor
   * @param ImageFileProcessorInterface $imageFileProcessor
   * @param SettingsService $settingsService
   * @param iterable<ImageTransformationStrategyInterface> $strategies
   */
  public function __construct(
    private ImageProcessorInterface     $imageProcessor,
    private ImageFileProcessorInterface $imageFileProcessor,
    private SettingsService             $settingsService,
    #[AutowireIterator('image.transformation_strategy')]
    private iterable                    $strategies
  ) {
  }

  public function convertToFormat(SplFileInfo $file, ImageFormat $format, ?int $quality = null): File {
    $quality ??= $this->settingsService->get('image.compressionQuality');

    $extension = $format->getExtension();
    $fileName = $file->getBasename('.' . $file->getExtension());
    $newPath = sprintf('%s/%s.%s', $file->getPath(), $fileName, $extension);

    $this->imageFileProcessor->convertFormatFile(
      $file->getPathname(),
      $newPath,
      $format->value,
      $quality
    );

    return new File($newPath, true);
  }

  public function transform(
    ImageSource $source,
    ImageOptions $imageOptions
  ): string {
    $request = ImageTransformationRequest::fromImageOptions($imageOptions);

    $operations = $this->resolveOperations($request);

    $format = $this->resolveFormat($imageOptions, $request);
    $quality = $this->resolveQuality($imageOptions, $format);

    return $this->imageProcessor->process($source, $operations, $format, $quality, false);
  }

  public function stripExifMetadata(string $path): string {
    return $this->imageFileProcessor->stripMetadata($path);
  }

  /**
   * @return ImageOperation[]
   */
  private function resolveOperations(ImageTransformationRequest $request): array {
    $operations = [];

    foreach ($this->strategies as $strategy) {
      if (!$strategy->supports($request)) {
        continue;
      }

      foreach ($strategy->operations($request) as $operation) {
        $operations[] = $operation;
      }
    }

    return $operations;
  }

  private function resolveFormat(ImageOptions $imageOptions, ImageTransformationRequest $request): ?ImageFormat {
    $format = $imageOptions->getFormat();
    if ($format !== null) {
      return ImageFormat::fromString($format);
    }

    if ($imageOptions->getQuality() !== null && $request->getTargetDimensions() === null && !$request->hasPartialDimensions()) {
      return ImageFormat::JPEG;
    }

    return null;
  }

  private function resolveQuality(ImageOptions $imageOptions, ?ImageFormat $format): ?int {
    $quality = $imageOptions->getQuality();
    if ($quality !== null) {
      return $quality;
    }

    if ($format !== null) {
      return $this->settingsService->get('image.compressionQuality');
    }

    return null;
  }
}
