<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\ListSharesExists;

use Slink\Share\Domain\Filter\ShareListFilter;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class ListSharesExistsHandler implements QueryHandlerInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
  ) {
  }

  public function __invoke(ListSharesExistsQuery $query, ?string $userId = null): bool {
    $filter = new ShareListFilter(
      searchTerm: $query->getSearchTerm(),
      expiry: $query->getExpiry(),
      protection: $query->getProtection(),
      type: $query->getType(),
      shareableId: $query->getShareableId(),
      shareableType: $query->getShareableType(),
      userId: $userId,
    );

    return $this->shareRepository->existsByFilter($filter);
  }
}
