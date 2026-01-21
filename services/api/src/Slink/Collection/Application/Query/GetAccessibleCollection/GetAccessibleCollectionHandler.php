<?php

declare(strict_types=1);

namespace Slink\Collection\Application\Query\GetAccessibleCollection;

use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\ValueObject\ID;

final readonly class GetAccessibleCollectionHandler implements QueryHandlerInterface {
  public function __construct(
    private CollectionRepositoryInterface $collectionRepository,
    private ShareRepositoryInterface $shareRepository,
    private ShareServiceInterface $shareService,
  ) {
  }

  public function __invoke(GetAccessibleCollectionQuery $query, ?string $userId = null): ?Item {
    $collection = $this->collectionRepository->findById($query->getId());

    if ($collection === null) {
      return null;
    }

    $isOwner = ID::fromString($collection->getUserId())->equals(ID::fromUnknown($userId));
    $share = $this->shareRepository->findByShareable($query->getId(), ShareableType::Collection);

    if (!$isOwner && $share === null) {
      return null;
    }

    $extra = ['userId' => $collection->getUserId()];

    if ($isOwner && $share !== null) {
      $extra['shareInfo'] = [
        'shareId' => $share->getId(),
        'shareUrl' => $this->shareService->resolveUrl($share),
        'type' => ShareableType::Collection->value,
      ];
    }

    return Item::fromEntity($collection, $extra);
  }
}
