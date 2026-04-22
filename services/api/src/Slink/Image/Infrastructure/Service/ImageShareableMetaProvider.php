<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\Service;

use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareableMetaProviderInterface;

final readonly class ImageShareableMetaProvider implements ShareableMetaProviderInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
  ) {}

  public function supports(): ShareableType {
    return ShareableType::Image;
  }

  public function resolve(array $ids): array {
    if ($ids === []) {
      return [];
    }

    $meta = [];
    foreach ($this->imageRepository->findByIds($ids) as $image) {
      $fileName = $image->getFileName();
      $meta[$image->getUuid()] = [
        'id' => $image->getUuid(),
        'name' => $fileName,
        'previewUrl' => '/image/' . $fileName,
      ];
    }

    return $meta;
  }
}
