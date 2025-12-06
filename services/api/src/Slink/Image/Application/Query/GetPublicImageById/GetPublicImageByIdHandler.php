<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetPublicImageById;

use Slink\Bookmark\Domain\Repository\BookmarkRepositoryInterface;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetPublicImageByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private ImageRepositoryInterface $repository,
    private BookmarkRepositoryInterface $bookmarkRepository,
  ) {
  }

  public function __invoke(GetPublicImageByIdQuery $query, ?string $currentUserId = null): Item {
    $imageView = $this->repository->oneById($query->getId());

    if (!$imageView->getAttributes()->isPublic()) {
      throw new NotFoundException();
    }

    $isBookmarked = false;
    if ($currentUserId !== null) {
      $bookmarkedIds = $this->bookmarkRepository->getBookmarkedImageIds($currentUserId, [$imageView->getUuid()]);
      $isBookmarked = in_array($imageView->getUuid(), $bookmarkedIds, true);
    }

    return Item::fromEntity($imageView, ['isBookmarked' => $isBookmarked]);
  }
}
