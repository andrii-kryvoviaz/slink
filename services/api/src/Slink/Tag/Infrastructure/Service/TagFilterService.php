<?php

declare(strict_types=1);

namespace Slink\Tag\Infrastructure\Service;

use Slink\Image\Domain\Filter\TagFilterData;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Domain\Service\TagFilterServiceInterface;

final readonly class TagFilterService implements TagFilterServiceInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  public function createTagFilterData(array|null $originalTagIds, bool $requireAllTags = false, ?string $userId = null): TagFilterData {
    if (empty($originalTagIds) || !$userId) {
      return new TagFilterData();
    }

    $userIdObject = ID::fromString($userId);
    $allTags = $this->tagRepository->findByTagIds($originalTagIds, $userIdObject);
    $expandedTagIds = array_map(fn($tag) => $tag->getUuid(), $allTags);

    $tagGroupMap = [];
    if ($requireAllTags) {
      foreach ($originalTagIds as $originalTagId) {
        try {
          $tag = $this->tagRepository->oneById($originalTagId);
          $descendants = $this->tagRepository->findDescendantsByPaths([$tag->getPath()], $userIdObject);
          $descendantIds = array_map(fn($descendant) => $descendant->getUuid(), $descendants);
          $tagGroupMap[$originalTagId] = array_merge([$originalTagId], $descendantIds);
        } catch (\Exception $e) {
          $tagGroupMap[$originalTagId] = [$originalTagId];
        }
      }
    }

    return new TagFilterData(
      originalTagIds: $originalTagIds,
      expandedTagIds: $expandedTagIds,
      tagGroupMap: $tagGroupMap,
      requireAllTags: $requireAllTags
    );
  }
}