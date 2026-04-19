<?php

declare(strict_types=1);

namespace Slink\Share\Application\Service;

use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareableOwnerResolverInterface;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(ShareableOwnerResolverInterface::class)]
final readonly class ShareableOwnerResolver implements ShareableOwnerResolverInterface {
  public function __construct(
    private ImageRepositoryInterface $imageRepository,
    private CollectionRepositoryInterface $collectionRepository,
  ) {}

  public function resolveOwnerId(ShareableReference $shareable): ?string {
    return match ($shareable->getShareableType()) {
      ShareableType::Image => $this->resolveImageOwnerId($shareable->getShareableId()),
      ShareableType::Collection => $this->resolveCollectionOwnerId($shareable->getShareableId()),
    };
  }

  private function resolveImageOwnerId(string $imageId): ?string {
    try {
      $image = $this->imageRepository->oneById($imageId);
    } catch (\Throwable) {
      return null;
    }

    return $image->getUser()?->getUuid();
  }

  private function resolveCollectionOwnerId(string $collectionId): ?string {
    $collection = $this->collectionRepository->findById($collectionId);

    if ($collection === null) {
      return null;
    }

    return $collection->getUserId();
  }
}
