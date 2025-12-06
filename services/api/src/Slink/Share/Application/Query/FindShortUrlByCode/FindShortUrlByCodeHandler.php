<?php

declare(strict_types=1);

namespace Slink\Share\Application\Query\FindShortUrlByCode;

use Slink\Share\Domain\Repository\ShortUrlRepositoryInterface;
use Slink\Share\Infrastructure\ReadModel\View\ShortUrlView;
use Slink\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindShortUrlByCodeHandler implements QueryHandlerInterface {
  public function __construct(
    private ShortUrlRepositoryInterface $shortUrlRepository,
  ) {
  }

  public function __invoke(FindShortUrlByCodeQuery $query): ?ShortUrlView {
    return $this->shortUrlRepository->findByShortCode($query->getCode());
  }
}
