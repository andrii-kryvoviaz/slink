<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service;

use Slink\Image\Domain\Exception\UnauthorizedTagAccessException;
use Slink\Image\Domain\Image;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\Tag\Infrastructure\ReadModel\View\TagView;

final readonly class ImageTagAssigner {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  /**
   * @param array<string> $tagIds
   */
  public function assign(Image $image, array $tagIds, ?ID $userId): void {
    if ($userId === null || $tagIds === []) {
      return;
    }

    $ownedIds = array_map(
      static fn(TagView $tag) => $tag->getUuid(),
      $this->tagRepository->findExactTagsByIds($tagIds, $userId),
    );

    foreach ($tagIds as $tagId) {
      if (!in_array($tagId, $ownedIds, true)) {
        throw new UnauthorizedTagAccessException(ID::fromString($tagId), $userId);
      }

      $image->tagWith(ID::fromString($tagId), $userId);
    }
  }
}
