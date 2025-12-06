<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShareByTargetUrl;

use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindShareByTargetUrlHandler implements QueryHandlerInterface {
  public function __construct(
    private ShareRepositoryInterface $shareRepository,
  ) {
  }

  public function __invoke(FindShareByTargetUrlQuery $query): ?ShareView {
    return $this->shareRepository->findByTargetUrl($query->getTargetUrl());
  }
}
