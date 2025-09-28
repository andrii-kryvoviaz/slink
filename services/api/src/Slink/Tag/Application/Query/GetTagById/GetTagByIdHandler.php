<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagById;

use Doctrine\ORM\NonUniqueResultException;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\Tag\Domain\Exception\TagAccessDeniedException;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetTagByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  /**
   * @throws NonUniqueResultException
   * @throws NotFoundException
   */
  public function __invoke(GetTagByIdQuery $query, string $userId): Item {
    $tag = $this->tagRepository->oneById(ID::fromString($query->getId()));

    $ownerId = ID::fromString($tag->getUserId());
    $currentUserId = ID::fromString($userId);

    if (!$ownerId->equals($currentUserId)) {
      throw new TagAccessDeniedException();
    }

    return Item::fromEntity($tag);
  }
}