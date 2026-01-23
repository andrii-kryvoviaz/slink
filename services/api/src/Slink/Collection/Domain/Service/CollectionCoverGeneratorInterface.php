<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Service;

interface CollectionCoverGeneratorInterface {
  /**
   * @param string[] $imageIds
   */
  public function getCoverUrl(string $collectionId, array $imageIds): ?string;

  /**
   * @param string[] $imageIds
   */
  public function getCoverContent(string $collectionId, array $imageIds): ?string;

  public function invalidateCover(string $collectionId): void;
}
