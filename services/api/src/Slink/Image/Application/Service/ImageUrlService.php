<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Share\Domain\Service\ShareServiceInterface;

final readonly class ImageUrlService {
  public function __construct(
    private ShareServiceInterface $shareService
  ) {}

  public function generateImageUrl(string $fileName): string {
    $imageId = pathinfo($fileName, PATHINFO_FILENAME);
    $baseUrl = "/image/{$fileName}";

    return $this->shareService->share($imageId, $baseUrl)->getUrl();
  }

  public function generateThumbnailUrl(string $fileName, int $width = 300, int $height = 300): string {
    $imageId = pathinfo($fileName, PATHINFO_FILENAME);
    $baseUrl = "/image/{$fileName}?width={$width}&height={$height}&crop=true";

    return $this->shareService->share($imageId, $baseUrl)->getUrl();
  }
}
