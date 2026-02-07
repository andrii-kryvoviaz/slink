<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagChildren;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetTagChildrenHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetTagChildrenQuery $query, string $userId): Collection {
    $userId = ID::fromString($userId);
    $parentId = ID::fromString($query->getParentId());

    $parent = $this->tagRepository->oneById($parentId);
    if ($parent->getUserId() !== $userId->toString()) {
      throw new TagAccessDeniedException();
    }

    $tags = $this->tagRepository->findChildren($parentId, $userId);
    $items = array_map(fn($tag) => Item::fromPayload('Tag', $tag->toPayload()), $tags);

    return new Collection(
      1,
      count($tags),
      count($tags),
      $items
    );
  }
}
