<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetRootTags;

use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetRootTagsHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {
  }

  public function __invoke(GetRootTagsQuery $query, string $userId): Collection {
    $userId = ID::fromString($userId);

    $tags = $this->tagRepository->findRootTags($userId);
    $items = array_map(fn($tag) => Item::fromPayload('Tag', $tag->toPayload()), $tags);

    return new Collection(
      1,
      count($tags),
      count($tags),
      $items
    );
  }
}
