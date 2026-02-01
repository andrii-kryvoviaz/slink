<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Specification;

use Slink\Bookmark\Domain\Exception\PrivateImageBookmarkException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class PublicImageBookmarkSpecification implements PublicImageBookmarkSpecificationInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function ensureImageIsPublic(ID $imageId): void {
    $image = $this->imageRepository->oneById($imageId->toString());
    
    if (!$image->getAttributes()->isPublic()) {
      throw new PrivateImageBookmarkException();
    }
  }
}
