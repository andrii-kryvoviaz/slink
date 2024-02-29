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
  
  /**
   * @throws NotFoundException
   */
  public function __invoke(GetImageListQuery $query, int $page): Collection {
    $images = $this->repository->geImageList($page, new ImageListFilter(
      limit: $query->getLimit(),
      isPublic: $query->isPublic(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
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