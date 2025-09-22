<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetTagById;

use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetTagByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  public function __invoke(GetTagByIdQuery $query, string $userId): Item {
    $tag = $this->tagRepository->oneById($query->getId());

    if ($tag->getUserId() !== $userId) {
      throw new \InvalidArgumentException('You can only access your own tags');
    }

    return Item::fromEntity($tag);
  }
}