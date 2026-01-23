<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollectionCover;

use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Domain\Service\CollectionCoverGeneratorInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\ForbiddenException;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;

final readonly class GetCollectionCoverHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private CollectionCoverGeneratorInterface $coverGenerator,
  ) {
  }

  public function __invoke(GetCollectionCoverQuery $query, string $userId): string {
    $collection = $this->collectionRepository->findById($query->getId());

    if ($collection === null) {
      throw new NotFoundException();
    }

    $ownerId = ID::fromString($collection->getUserId());
    $currentUserId = ID::fromString($userId);

    if (!$ownerId->equals($currentUserId)) {
      throw new ForbiddenException();
    }

    $imageIds = $this->collectionItemRepository->getFirstImageIdsByCollectionIds([$query->getId()], 5);
    $collectionImageIds = $imageIds[$query->getId()] ?? [];

    if (empty($collectionImageIds)) {
      throw new NotFoundException();
    }

    $content = $this->coverGenerator->getCoverContent($query->getId(), $collectionImageIds);

    if ($content === null) {
      throw new NotFoundException();
    }

    return $content;
  }
}
