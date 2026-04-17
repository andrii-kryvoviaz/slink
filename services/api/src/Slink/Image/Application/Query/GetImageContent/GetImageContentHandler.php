<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageContent;

use Slink\Image\Domain\Enum\ImageAccess;
use Slink\Image\Domain\Enum\ImageFormat;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Domain\Service\ImageAnalyzerInterface;
use Slink\Image\Domain\Service\ImageSanitizerInterface;
use Slink\Image\Domain\Service\ImageRetrievalInterface;
use Slink\Image\Domain\Service\ImageUrlSignatureInterface;
use Slink\Image\Domain\ValueObject\ImageAccessContext;
use Slink\Share\Domain\Service\ShareUrlBuilderInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ImageOptions;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetImageContentHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageAnalyzerInterface $imageAnalyzer,
    private ImageRepositoryInterface $repository,
    private ImageRetrievalInterface $imageRetrievalService,
    private ImageSanitizerInterface $sanitizer,
    private ImageUrlSignatureInterface $transformSignature,
    private AuthorizationCheckerInterface $access,
    private ShareUrlBuilderInterface $shareUrlBuilder,
  ) {
  }

  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageContentQuery $query, string $fileName, ?string $requestedFormat = null): Item {
    $imageId = $this->extractImageId($fileName);
    $imageView = $this->repository->oneById($imageId);

    $query = $this->sanitizeTransforms($query, $imageId);

    $targetPath = $this->shareUrlBuilder->buildTargetPath(
      $imageId,
      $imageView->getFileName(),
      $query->getWidth(),
      $query->getHeight(),
      $query->isCropped(),
      $requestedFormat,
      $query->getFilter(),
    );

    $context = new ImageAccessContext(
      $imageView,
      $query->getScopeCollectionId(),
      $query->getScopeSignature(),
      $targetPath,
    );

    if (!$this->access->isGranted(ImageAccess::View, $context)) {
      throw new NotFoundException();
    }

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

    $imageContent = $this->imageRetrievalService->getImage($imageOptions);

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

  private function sanitizeTransforms(GetImageContentQuery $query, string $imageId): GetImageContentQuery {
    if (!$query->hasTransformParams()) {
      return $query;
    }

    $signature = $query->getTransformSignature();

    if ($signature === null) {
      return $query->withoutTransformParams();
    }

    if (!$this->transformSignature->verify($imageId, $query->getSignedTransformParams(), $signature)) {
      return $query->withoutTransformParams();
    }

    return $query;
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
