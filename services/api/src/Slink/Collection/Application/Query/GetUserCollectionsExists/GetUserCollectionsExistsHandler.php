<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetUserCollectionsExists;

use Slink\Collection\Domain\Filter\CollectionListFilter;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class GetUserCollectionsExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
  ) {
  }

  public function __invoke(GetUserCollectionsExistsQuery $query, string $userId): bool {
    $filter = new CollectionListFilter(
      userId: $userId,
      searchTerm: $query->getSearchTerm(),
    );

    return $this->collectionRepository->existsByFilter($filter);
  }
}
