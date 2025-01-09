<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageListById;

use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetImageListByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository
  ) {
  }
  
  /**
   * @return array<int,mixed>
   */
  public function __invoke(GetImageListByIdQuery $query, string $userId): array {
    $images = $this->repository->geImageList(1, new ImageListFilter(
      userId: $userId,
      uuids: $query->getUuid()
    ));
    
    return array_map(fn($image) => Item::fromEntity($image), iterator_to_array($images));
  }
}