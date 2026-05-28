<?php

declare(strict_types=1);

namespace Slink\Collection\Domain\Service;

interface CollectionCoverGeneratorInterface {
  /**
   * @param string[] $imageIds
   */
  public function getCoverUrl(string $collectionId, array $imageIds): ?string;

  /**
   * @param list<string> $collectionIds
   * @return array<string, ?string> collectionId => coverUrl|null
   */
  public function getCoverUrlsByIds(array $collectionIds): array;

  /**
   * @param string[] $imageIds
   */
  public function getCoverContent(string $collectionId, array $imageIds): ?string;

  public function invalidateCover(string $collectionId): void;
}
