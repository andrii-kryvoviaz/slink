<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Mapping;

use Slink\Shared\Domain\Exception\Date\DateTimeException;
use Slink\Shared\Domain\ValueObject\Date\DateTime;

final readonly class AnalyticsCountable {
  private DateTime $date;
  
  /**
   * @throws DateTimeException
   */
  public function __construct(
    DateTime|string $date,
    private int $count,
  ) {
    $this->date = DateTime::fromUnknown($date);
  }
  
  public function getDate(): DateTime {
    return $this->date;
  }
  
  public function getFormattedDate(string $format): string {
    return $this->date->format($format);
  }
  
  public function getCount(): int {
    return $this->count;
  }
}