<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageListById;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetImageListByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }
  
  /**
   * @return array<int,mixed>
   */
  public function __invoke(GetImageListByIdQuery $query, string $userId): array {
    $images = $this->repository->geImageList(new ImageListFilter(
      userId: $userId,
      uuids: $query->getUuid()
    ));
    
    $bookmarkCounts = $this->bookmarkRepository->countByImageIds($query->getUuid());

    return array_map(
      fn($image) => Item::fromEntity($image, ['bookmarkCount' => $bookmarkCounts[$image->getUuid()] ?? 0]),
      iterator_to_array($images),
    );
  }
}