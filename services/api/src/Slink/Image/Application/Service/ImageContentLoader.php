<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageRetrievalInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\ValueObject\ImageContent;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

readonly class ImageContentLoader {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageRetrievalInterface $imageRetrievalService,
    private ImageSanitizerInterface $sanitizer,
  ) {
  }

  /**
   * @param array<string, mixed> $transforms
   * @throws NotFoundException
   */
  public function load(
    ImageView $imageView,
    ?string $requestedFormat = null,
    array $transforms = [],
  ): ImageContent {
    $originalMimeType = $imageView->getMimeType();
    $targetFormat = $requestedFormat !== null ? ImageFormat::fromString($requestedFormat) : null;
    $needsConversion = $this->needsFormatConversion($originalMimeType, $targetFormat);

    $effectiveParams = $this->imageAnalyzer->supportsResize($originalMimeType)
      ? $transforms
      : [];

    if ($needsConversion && $targetFormat !== null) {
      $effectiveParams['format'] = $targetFormat->getExtension();
    }

    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $imageView->getFileName(),
      'mimeType' => $originalMimeType,
      ...$effectiveParams,
    ]);

    $imageContent = $this->imageRetrievalService->getImage($imageOptions);

    if ($imageContent === null) {
      throw new NotFoundException();
    }

    if ($this->sanitizer->requiresSanitization($originalMimeType)) {
      $imageContent = $this->sanitizer->sanitize($imageContent);
    }

    $responseMimeType = $needsConversion && $targetFormat !== null
      ? $targetFormat->getMimeType()
      : $originalMimeType;

    return new ImageContent($imageContent, $responseMimeType);
  }

  private function needsFormatConversion(?string $originalMimeType, ?ImageFormat $targetFormat): bool {
    if ($targetFormat === null || $originalMimeType === null) {
      return false;
    }

    $originalFormat = ImageFormat::fromMimeType($originalMimeType);
    if ($originalFormat === null) {
      return false;
    }

    return $originalFormat !== $targetFormat;
  }
}
