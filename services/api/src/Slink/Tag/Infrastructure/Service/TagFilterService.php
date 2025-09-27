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
    
    if (empty($allTags)) {
      return new TagFilterData();
    }
    
    $tagPaths = array_map(fn($tag) => $tag->getPath(), $allTags);

    return new TagFilterData(
      originalTagIds: $originalTagIds,
      requireAllTags: $requireAllTags,
      tagPaths: $tagPaths
    );
  }
}