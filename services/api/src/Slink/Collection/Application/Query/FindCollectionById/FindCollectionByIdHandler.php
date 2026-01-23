<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\FindCollectionById;

use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindCollectionByIdHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
  ) {
  }

  public function __invoke(FindCollectionByIdQuery $query): ?CollectionView {
    return $this->collectionRepository->findById($query->getId());
  }
}
