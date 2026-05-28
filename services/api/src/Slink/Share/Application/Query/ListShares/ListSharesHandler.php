<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\ListShares;

use Slink\Share\Domain\Filter\ShareListFilter;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\Resource\ShareListItem;
use Slink\Share\Infrastructure\Resource\ShareableMetaResolver;
use Slink\Shared\Application\Http\Collection;
use Slink\Shared\Application\Http\Item;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;

final readonly class ListSharesHandler implements QueryHandlerInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
    private ShareServiceInterface $shareService,
    private ShareableMetaResolver $metaResolver,
    private CursorPaginator $cursorPaginator,
  ) {}

  public function __invoke(ListSharesQuery $query, string $userId): Collection {
    $filter = new ShareListFilter(
      limit: $query->getLimit(),
      orderBy: $query->getOrderBy(),
      order: $query->getOrder(),
      cursor: $query->getCursor(),
      searchTerm: $query->getSearchTerm(),
      expiry: $query->getExpiry(),
      protection: $query->getProtection(),
      type: $query->getType(),
      shareableId: $query->getShareableId(),
      shareableType: $query->getShareableType(),
      userId: $userId,
    );

    /** @var list<ShareView> $shares */
    $shares = iterator_to_array($this->shareRepository->getShareList($filter));
    $total = $this->shareRepository->countShareList($filter);

    $resolveMeta = $this->metaResolver->resolver($shares);

    $items = array_map(
      fn(ShareView $share): Item => Item::fromEntity(
        new ShareListItem(
          view: $share,
          url: $this->shareService->resolveUrl($share),
          shareableMeta: $resolveMeta(
            $share->getShareable()->getShareableId(),
            $share->getShareable()->getShareableType(),
          ),
        ),
        extra: ['id' => $share->getId()],
      ),
      $shares,
    );

    $paginated = $this->cursorPaginator->paginate($items, $query->getLimit());

    return Collection::fromCursorPaginator($paginated, limit: $query->getLimit(), total: $total);
  }
}
