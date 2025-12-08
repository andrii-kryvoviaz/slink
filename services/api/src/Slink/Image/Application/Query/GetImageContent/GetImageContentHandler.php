<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Shared\Infrastructure\FileSystem\Storage\Contract\StorageInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageRepositoryInterface $repository,
    private StorageInterface $storage,
    private ImageSanitizerInterface $sanitizer,
  ) {
  }
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query, string $fileName, ?string $requestedFormat = null): Item {
    $imageId = $this->extractImageId($fileName);
    $imageView = $this->repository->oneById($imageId);
    
    $originalMimeType = $imageView->getMimeType();
    $targetFormat = $requestedFormat ? ImageFormat::fromString($requestedFormat) : null;
    $needsConversion = $this->needsFormatConversion($originalMimeType, $targetFormat);
    
    $transformParams = $this->imageAnalyzer->supportsResize($originalMimeType)
      ? $query->getTransformParams()
      : [];
    
    if ($needsConversion && $targetFormat) {
      $transformParams['format'] = $targetFormat->getExtension();
    }
    
    $imageOptions = ImageOptions::fromPayload([
      'fileName' => $imageView->getFileName(),
      'mimeType' => $originalMimeType,
      ...$transformParams
    ]);
    
    $imageContent = $this->storage->getImage($imageOptions);
    
    if($imageContent === null) {
      throw new NotFoundException();
    }
    
    if($this->sanitizer->requiresSanitization($originalMimeType)) {
      $imageContent = $this->sanitizer->sanitize($imageContent);
    }
    
    $responseMimeType = $needsConversion && $targetFormat 
      ? $targetFormat->getMimeType() 
      : $originalMimeType;
    
    return Item::fromContent($imageContent, $responseMimeType);
  }

  private function extractImageId(string $fileName): string {
    $lastDotIndex = strrpos($fileName, '.');
    return $lastDotIndex !== false ? substr($fileName, 0, $lastDotIndex) : $fileName;
  }

  private function needsFormatConversion(?string $originalMimeType, ?ImageFormat $targetFormat): bool {
    if (!$targetFormat || !$originalMimeType) {
      return false;
    }
    
    $originalFormat = ImageFormat::fromMimeType($originalMimeType);
    if (!$originalFormat) {
      return false;
    }
    
    return $originalFormat !== $targetFormat;
  }
}