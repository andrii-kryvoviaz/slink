<?php

declare(strict_types=1);

namespace Slink\Collection\Infrastructure\Service;

use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\ShareableMetaProviderInterface;

final readonly class CollectionShareableMetaProvider implements ShareableMetaProviderInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private CollectionCoverGeneratorInterface $coverGenerator,
  ) {}

  public function supports(): ShareableType {
    return ShareableType::Collection;
  }

  public function resolve(array $ids): array {
    if ($ids === []) {
      return [];
    }

    $coverUrls = $this->coverGenerator->getCoverUrlsByIds($ids);

    $meta = [];
    foreach ($this->collectionRepository->findByIds($ids) as $collection) {
      $id = $collection->getId();
      $meta[$id] = [
        'id' => $id,
        'name' => $collection->getName(),
        'previewUrl' => $coverUrls[$id] ?? null,
      ];
    }

    return $meta;
  }
}
