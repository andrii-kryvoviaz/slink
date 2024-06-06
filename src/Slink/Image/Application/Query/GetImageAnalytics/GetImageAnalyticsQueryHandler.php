<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageAnalytics;

use Slink\Image\Domain\Repository\ImageAnalyticsRepositoryInterface;
use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\Shared\Domain\Exception\Date\DateTimeException;

final readonly class GetImageAnalyticsQueryHandler implements QueryHandlerInterface {
  
  public function __construct(
    private ImageAnalyticsRepositoryInterface $repository
  ) {
  }
  
  /**
   * @param GetImageAnalyticsQuery $query
   * @return array<string, mixed>
   * @throws DateTimeException
   */
  public function __invoke(GetImageAnalyticsQuery $query): array {
    return $this->repository->getAnalytics($query->getDateRange());
  }
}