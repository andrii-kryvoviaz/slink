<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetAccessibleCollection;

use Slink\Collection\Domain\Enum\CollectionAccess;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Service\OwnerShareInfoResolverInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetAccessibleCollectionHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private OwnerShareInfoResolverInterface $ownerShareInfoResolver,
    private AuthorizationCheckerInterface $access,
  ) {
  }

  public function __invoke(GetAccessibleCollectionQuery $query, ?string $userId = null): ?Item {
    $collection = $this->collectionRepository->findById($query->getId());

    if ($collection === null) {
      return null;
    }

    if (!$this->access->isGranted(CollectionAccess::View, $collection)) {
      return null;
    }

    $extra = $this->ownerShareInfoResolver->resolve(
      $query->getId(),
      ShareableType::Collection,
      ID::fromUnknown($collection->getUserId()),
      ID::fromUnknown($userId),
    );

    return Item::fromEntity($collection, $extra);
  }
}
