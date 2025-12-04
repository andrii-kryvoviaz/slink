<?php

declare(strict_types=1);

namespace Slink\Bookmark\Domain\Specification;

use Slink\Bookmark\Domain\Exception\SelfBookmarkException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class SelfBookmarkSpecification implements SelfBookmarkSpecificationInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  public function ensureNotSelfBookmark(ID $imageId, ID $userId): void {
    $image = $this->imageRepository->oneById($imageId->toString());
    $imageOwner = $image->getUser();

    if ($imageOwner !== null && $imageOwner->getUuid() === $userId->toString()) {
      throw new SelfBookmarkException();
    }
  }
}
