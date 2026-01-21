<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShareByShareable;

use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindShareByShareableHandler implements QueryHandlerInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
  ) {
  }

  public function __invoke(FindShareByShareableQuery $query): ?ShareView {
    return $this->shareRepository->findByShareable(
      $query->getShareableId(),
      $query->getShareableType(),
    );
  }
}
