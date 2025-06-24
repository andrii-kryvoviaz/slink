<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageList;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetImageListHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository
  ) {
  }
  
  public function __invoke(GetImageListQuery $query, int $page, ?bool $isPublic = null, ?string $userId = null): Collection {
    $images = $this->repository->geImageList($page, new ImageListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      isPublic: $isPublic,
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
      searchBy: $query->getSearchBy()
    ));
    
    $items = array_map(fn($image) => Item::fromEntity($image), iterator_to_array($images));
    
    return new Collection(
      $page,
      $query->getLimit(),
      $images->count(),
      $items
    );
  }
}