<?php

declare(strict_types=1);

namespace Slink\Tag\Application\Query\GetImageTags;

use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;

final readonly class GetImageTagsHandler implements QueryHandlerInterface {
  public function __construct(
    private TagRepositoryInterface $tagRepository,
  ) {}

  public function __invoke(GetImageTagsQuery $query): Collection {
    $imageId = ID::fromString($query->getImageId());
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