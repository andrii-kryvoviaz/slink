<?php

declare(strict_types=1);

namespace Slink\Image\Application\Query\GetImageAnalytics;

use Slink\Shared\Application\Query\QueryInterface;
use Slink\Shared\Domain\Enum\Date\DateInterval;
use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateRange;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetImageAnalyticsQuery implements QueryInterface {
  public function __construct(
    #[Assert\Choice(callback: [DateInterval::class, 'values'])]
    private string $dateInterval = DateInterval::LAST_7_DAYS->value
  ) {
  }
  
  /**
   * @return DateRange
   * @throws DateTimeException
   */
  public function getDateRange(): DateRange {
    $dateInterval = DateInterval::from($this->dateInterval);
    return DateRange::fromDateInterval($dateInterval);
  }
}