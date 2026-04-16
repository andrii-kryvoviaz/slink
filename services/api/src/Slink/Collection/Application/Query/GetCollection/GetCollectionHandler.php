<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetCollection;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Exception\CollectionAccessDeniedException;
use Slink\Collection\Domain\Repository\CollectionItemRepositoryInterface;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetCollectionHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private CollectionItemRepositoryInterface $collectionItemRepository,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(GetCollectionQuery $query, string $userId): ?Item {
    $collection = $this->collectionRepository->findById($query->getId());

    if ($collection === null) {
      return null;
    }

    if (!$this->access->isGranted(CollectionAccess::Edit, $collection)) {
      throw new CollectionAccessDeniedException();
    }

    $itemCount = $this->collectionItemRepository->countByCollectionId($query->getId());

    return Item::fromEntity($collection, ['itemCount' => $itemCount]);
  }
}
