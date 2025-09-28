<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetImageTags;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetImageTagsHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface   $tagRepository,
    private ImageRepositoryInterface $imageRepository,
  ) {
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetImageTagsQuery $query, string $userId): Collection {
    $imageId = ID::fromString($query->getImageId());

    $image = $this->imageRepository->oneById($imageId->toString());
    $ownerUuid = $image->getUser()?->getUuid();

    $ownerId = $ownerUuid ? ID::fromString($ownerUuid) : null;
    $currentUserId = ID::fromString($userId);

    if (!$ownerId || !$ownerId->equals($currentUserId)) {
      throw new TagAccessDeniedException();
    }

    $tags = $this->tagRepository->findByImageId($imageId);
    $items = array_map(fn($tag) => Item::fromEntity($tag), $tags);

    return new Collection(
      1,
      count($tags),
      count($tags),
      $items
    );
  }
}