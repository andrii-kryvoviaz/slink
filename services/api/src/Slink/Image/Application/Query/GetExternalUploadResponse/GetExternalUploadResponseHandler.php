<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetExternalUploadResponse;

use Slink\Image\Application\Service\ImageUrlService;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetExternalUploadResponseHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageUrlService $imageUrlService
  ) {}

  /**
   * @return array<string, mixed>
   */
  public function __invoke(GetExternalUploadResponseQuery $query): array {
    $imageId = $query->getImageId();
    $fileName = $query->getFileName();

    return [
      'url' => $this->imageUrlService->generateImageUrl($fileName),
      'thumbnailUrl' => $this->imageUrlService->generateThumbnailUrl($fileName),
      'id' => $imageId
    ];
  }
}
