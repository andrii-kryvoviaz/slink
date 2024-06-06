<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Repository;

use Slink\Shared\Domain\ValueObject\Date\DateRange;

interface ImageAnalyticsRepositoryInterface {
  /**
   * @param DateRange $dateRange
   * @return array<string, mixed>
   */
  public function getAnalytics(DateRange $dateRange): array;
}